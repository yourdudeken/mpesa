import axios, { AxiosInstance, AxiosRequestConfig } from 'axios';

export class HttpClient {
  private client: AxiosInstance;
  private baseUrl: string;

  constructor(baseUrl: string) {
    this.baseUrl = baseUrl;
    this.client = axios.create({
      baseURL: baseUrl,
    });
  }

  async post(url: string, body: any, token: string): Promise<any> {
    const config: AxiosRequestConfig = {
      headers: {
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    };

    try {
      const response = await this.client.post(url, body, config);
      return response.data;
    } catch (error: any) {
      throw new Error(`Mpesa request failed: ${error.response?.data?.errorMessage || error.message}`);
    }
  }

  async get(url: string, token: string): Promise<any> {
    const config: AxiosRequestConfig = {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    };

    try {
      const response = await this.client.get(url, config);
      return response.data;
    } catch (error: any) {
      throw new Error(`Mpesa request failed: ${error.response?.data?.errorMessage || error.message}`);
    }
  }

  getBaseUrl(): string {
    return this.baseUrl;
  }
}
