import { B2bParams } from '../types';
import { HttpClient } from '../http/httpClient';
import { Auth } from '../core/Auth';
import { Helpers } from '../utils/helpers';

export class B2BService {
  private httpClient: HttpClient;
  private auth: Auth;
  private helpers: Helpers;
  private baseUrl: string;

  constructor(httpClient: HttpClient, auth: Auth) {
    this.httpClient = httpClient;
    this.auth = auth;
    this.helpers = new Helpers();
    this.baseUrl = httpClient.getBaseUrl();
  }

  async send(params: B2bParams): Promise<any> {
    const { receiverShortcode, commandId, amount, remarks, accountNumber, resultUrl, timeoutUrl, shortCodeType = 'B2B' } = params;

    if (commandId === 'BusinessPayBill' && !accountNumber) {
      throw new Error('Account Number is required for BusinessPayBill CommandID');
    }

    const url = `${this.baseUrl}/mpesa/b2b/v1/paymentrequest`;
    const body = {
      Initiator: this.helpers.getConfig('initiatorName'),
      SecurityCredential: this.helpers.generateSecurityCredential(),
      CommandID: commandId,
      SenderIdentifierType: '4',
      RecieverIdentifierType: '4',
      Amount: amount,
      PartyA: this.helpers.getConfig('b2cShortcode'),
      PartyB: receiverShortcode,
      AccountReference: accountNumber,
      Remarks: remarks,
      ResultURL: resultUrl || this.helpers.getConfig('callbacks.b2bResultUrl'),
      QueueTimeOutURL: timeoutUrl || this.helpers.getConfig('callbacks.b2bTimeoutUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, body, token);
  }
}
