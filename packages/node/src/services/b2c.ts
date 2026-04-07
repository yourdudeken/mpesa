import { B2cParams } from '../types';
import { HttpClient } from '../http/httpClient';
import { Auth } from '../core/Auth';
import { Helpers } from '../utils/helpers';

export class B2CService {
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

  async send(params: B2cParams): Promise<any> {
    const { phonenumber, commandId, amount, remarks, resultUrl, timeoutUrl, shortCodeType = 'B2C' } = params;

    const url = `${this.baseUrl}/mpesa/b2c/v1/paymentrequest`;
    const body = {
      InitiatorName: this.helpers.getConfig('initiatorName'),
      SecurityCredential: this.helpers.generateSecurityCredential(),
      CommandID: commandId,
      Amount: amount,
      PartyA: this.helpers.getConfig('b2cShortcode'),
      PartyB: this.helpers.phoneValidator(phonenumber),
      Remarks: remarks,
      Occassion: '',
      ResultURL: resultUrl || this.helpers.getConfig('callbacks.b2cResultUrl'),
      QueueTimeOutURL: timeoutUrl || this.helpers.getConfig('callbacks.b2cTimeoutUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, body, token);
  }

  async validated(params: B2cParams & { idNumber: string }): Promise<any> {
    const { phonenumber, commandId, amount, remarks, idNumber, resultUrl, timeoutUrl, shortCodeType = 'B2C' } = params;

    const url = `${this.baseUrl}/mpesa/b2cvalidate/v2/paymentrequest`;
    const body = {
      InitiatorName: this.helpers.getConfig('initiatorName'),
      SecurityCredential: this.helpers.generateSecurityCredential(),
      CommandID: commandId,
      Amount: amount,
      PartyA: this.helpers.getConfig('b2cShortcode'),
      PartyB: this.helpers.phoneValidator(phonenumber),
      Remarks: remarks,
      Occassion: '',
      OriginatorConversationID: this.helpers.getFormattedTimestamp(),
      IDType: '01',
      IDNumber: idNumber,
      ResultURL: resultUrl || this.helpers.getConfig('callbacks.b2cResultUrl'),
      QueueTimeOutURL: timeoutUrl || this.helpers.getConfig('callbacks.b2cTimeoutUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, body, token);
  }
}
