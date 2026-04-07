import { StkPushParams } from '../types';
import { HttpClient } from '../http/httpClient';
import { Auth } from '../core/Auth';
import { Helpers } from '../utils/helpers';

export class STKPushService {
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

  async push(params: StkPushParams): Promise<any> {
    const { phonenumber, amount, accountNumber, callbackUrl, transactionType = 'CustomerPayBillOnline', shortCodeType = 'C2B' } = params;

    if (!accountNumber) {
      throw new Error('An Account Reference is required for All transactions.');
    }

    const url = `${this.baseUrl}/mpesa/stkpush/v1/processrequest`;
    const data = {
      BusinessShortCode: this.helpers.getConfig('shortcode'),
      Password: this.helpers.lipaNaMpesaPassword(),
      Timestamp: this.helpers.getFormattedTimestamp(),
      Amount: amount,
      PartyA: this.helpers.phoneValidator(phonenumber),
      PartyB: transactionType === 'CustomerPayBillOnline' ? this.helpers.getConfig('shortcode') : this.helpers.getConfig('tillNumber'),
      TransactionType: transactionType,
      PhoneNumber: this.helpers.phoneValidator(phonenumber),
      TransactionDesc: 'Payment',
      AccountReference: accountNumber,
      CallBackURL: callbackUrl || this.helpers.getConfig('callbacks.callbackUrl'),
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, data, token);
  }

  async query(checkoutRequestId: string, shortCodeType: string = 'C2B'): Promise<any> {
    const url = `${this.baseUrl}/mpesa/stkpushquery/v1/query`;
    const data = {
      BusinessShortCode: this.helpers.getConfig('shortcode'),
      Password: this.helpers.lipaNaMpesaPassword(),
      Timestamp: this.helpers.getFormattedTimestamp(),
      CheckoutRequestID: checkoutRequestId,
    };

    const token = await this.auth.getAccessToken(shortCodeType);
    return this.httpClient.post(url, data, token);
  }
}
