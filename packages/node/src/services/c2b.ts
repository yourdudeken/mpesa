import { C2bRegisterParams, C2bSimulateParams } from '../types';
import { HttpClient } from '../http/httpClient';
import { Auth } from '../core/Auth';
import { Helpers } from '../utils/helpers';

export class C2BService {
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

  async registerURLS(params: C2bRegisterParams): Promise<any> {
    const { shortcode, confirmUrl, validateUrl, shortCodeType = 'C2B' } = params;

    const url = `${this.baseUrl}/mpesa/c2b/v2/registerurl`;
    const body = {
      ShortCode: shortcode,
      ResponseType: 'Completed',
      ConfirmationURL: confirmUrl || this.helpers.getConfig('callbacks.c2bConfirmationUrl'),
      ValidationURL: validateUrl || this.helpers.getConfig('callbacks.c2bValidationUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, body, token);
  }

  async simulate(params: C2bSimulateParams): Promise<any> {
    const { phonenumber, amount, shortcode, commandId, accountNumber, shortCodeType = 'C2B' } = params;

    const url = `${this.baseUrl}/mpesa/c2b/v2/simulate`;
    const data = commandId === 'CustomerPayBillOnline' ? {
      Msisdn: this.helpers.phoneValidator(phonenumber),
      Amount: amount,
      BillRefNumber: accountNumber,
      CommandID: commandId,
      ShortCode: shortcode,
    } : {
      Msisdn: this.helpers.phoneValidator(phonenumber),
      Amount: amount,
      CommandID: commandId,
      ShortCode: shortcode,
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, data, token);
  }
}
