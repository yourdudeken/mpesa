import { AccountBalanceParams, TransactionStatusParams, ReversalParams } from '../types';
import { HttpClient } from '../http/httpClient';
import { Auth } from '../core/Auth';
import { Helpers } from '../utils/helpers';

export class AccountService {
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

  async balance(params: AccountBalanceParams): Promise<any> {
    const { shortcode, identifierType, remarks, resultUrl, timeoutUrl, shortCodeType = 'C2B' } = params;

    const url = `${this.baseUrl}/mpesa/accountbalance/v1/query`;
    const body = {
      Initiator: this.helpers.getConfig('initiatorName'),
      SecurityCredential: this.helpers.generateSecurityCredential(),
      CommandID: 'AccountBalance',
      PartyA: shortcode,
      IdentifierType: identifierType,
      Remarks: remarks,
      ResultURL: resultUrl || this.helpers.getConfig('callbacks.balanceResultUrl'),
      QueueTimeOutURL: timeoutUrl || this.helpers.getConfig('callbacks.balanceTimeoutUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, body, token);
  }

  async status(params: TransactionStatusParams): Promise<any> {
    const { shortcode, transactionId, identifierType, remarks, resultUrl, timeoutUrl, shortCodeType = 'C2B' } = params;

    const url = `${this.baseUrl}/mpesa/transactionstatus/v1/query`;
    const body = {
      Initiator: this.helpers.getConfig('initiatorName'),
      SecurityCredential: this.helpers.generateSecurityCredential(),
      CommandID: 'TransactionStatusQuery',
      TransactionID: transactionId,
      PartyA: shortcode,
      IdentifierType: identifierType,
      Remarks: remarks,
      Occassion: '',
      ResultURL: resultUrl || this.helpers.getConfig('callbacks.statusResultUrl'),
      QueueTimeOutURL: timeoutUrl || this.helpers.getConfig('callbacks.statusTimeoutUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, body, token);
  }

  async reversal(params: ReversalParams): Promise<any> {
    const { shortcode, transactionId, amount, remarks, resultUrl, timeoutUrl, shortCodeType = 'C2B' } = params;

    const url = `${this.baseUrl}/mpesa/reversal/v1/request`;
    const body = {
      Initiator: this.helpers.getConfig('initiatorName'),
      SecurityCredential: this.helpers.generateSecurityCredential(),
      CommandID: 'TransactionReversal',
      TransactionID: transactionId,
      Amount: amount,
      ReceiverParty: shortcode,
      RecieverIdentifierType: '11',
      Remarks: remarks,
      Occasion: '',
      ResultURL: resultUrl || this.helpers.getConfig('callbacks.reversalResultUrl'),
      QueueTimeOutURL: timeoutUrl || this.helpers.getConfig('callbacks.reversalTimeoutUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, body, token);
  }
}
