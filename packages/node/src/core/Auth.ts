import axios from 'axios';
import { MpesaConfig } from '../core/Config';

export class Auth {
  private config: MpesaConfig;
  private baseUrl: string;
  private accessToken: string | null = null;
  private tokenExpiry: number = 0;

  constructor(config: MpesaConfig) {
    this.config = config;
    this.baseUrl = config.environment === 'sandbox'
      ? 'https://sandbox.safaricom.co.ke'
      : 'https://api.safaricom.co.ke';
  }

  async getAccessToken(shortCodeType: string = 'C2B'): Promise<string> {
    if (this.accessToken && Date.now() < this.tokenExpiry) {
      return this.accessToken;
    }

    const consumerKey = (shortCodeType === 'B2C' || shortCodeType === 'B2B')
      ? this.config.b2cConsumerKey
      : this.config.mpesaConsumerKey;
    const consumerSecret = (shortCodeType === 'B2C' || shortCodeType === 'B2B')
      ? this.config.b2cConsumerSecret
      : this.config.mpesaConsumerSecret;

    if (!consumerKey || !consumerSecret) {
      throw new Error('Consumer key and secret are required');
    }

    const auth = Buffer.from(`${consumerKey}:${consumerSecret}`).toString('base64');

    try {
      const response = await axios.get(`${this.baseUrl}/oauth/v1/generate?grant_type=client_credentials`, {
        headers: { Authorization: `Basic ${auth}` },
      });
      
      this.accessToken = response.data.access_token;
      this.tokenExpiry = Date.now() + (response.data.expires_in - 60) * 1000;
      return this.accessToken;
    } catch (error: any) {
      throw new Error(`Failed to generate access token: ${error.message}`);
    }
  }

  clearToken(): void {
    this.accessToken = null;
    this.tokenExpiry = 0;
  }
}
