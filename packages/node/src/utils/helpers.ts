import * as crypto from 'crypto';
import * as fs from 'fs';
import * as path from 'path';
import { MpesaConfig } from '../core/Config';

export class Helpers {
  private config: MpesaConfig;

  constructor(config?: MpesaConfig) {
    this.config = config as MpesaConfig;
  }

  setConfig(config: MpesaConfig): void {
    this.config = config;
  }

  getConfig(key: string): any {
    if (!this.config) return undefined;
    const keys = key.split('.');
    let value: any = this.config;
    for (const k of keys) {
      value = value?.[k];
    }
    return value;
  }

  phoneValidator(phoneNo: string): string {
    phoneNo = phoneNo.startsWith('+') ? phoneNo.substring(1) : phoneNo;
    phoneNo = phoneNo.startsWith('0') ? '254' + phoneNo.substring(1) : phoneNo;
    phoneNo = phoneNo.startsWith('7') ? '254' + phoneNo : phoneNo;
    return phoneNo;
  }

  getFormattedTimestamp(): string {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    return `${year}${month}${day}${hours}${minutes}${seconds}`;
  }

  lipaNaMpesaPassword(): string {
    const timestamp = this.getFormattedTimestamp();
    const password = this.getConfig('shortcode') + this.getConfig('passkey') + timestamp;
    return Buffer.from(password).toString('base64');
  }

  generateSecurityCredential(): string {
    const certPath = this.getConfig('environment') === 'sandbox'
      ? path.join(__dirname, '../certificates/SandboxCertificate.cer')
      : path.join(__dirname, '../certificates/ProductionCertificate.cer');
    
    const pubkey = fs.readFileSync(certPath);
    const password = this.getConfig('initiatorPassword');
    
    const encrypted = crypto.publicEncrypt(
      { key: pubkey, padding: crypto.constants.RSA_PKCS1_PADDING },
      Buffer.from(password)
    );
    
    return encrypted.toString('base64');
  }

  resolveCallbackUrl(paramUrl: string | undefined, configKey: string): string {
    const configUrl = this.getConfig(`callbacks.${configKey}`);
    if (paramUrl) return paramUrl;
    if (configUrl) return configUrl;
    throw new Error(`Ensure you have set the ${configKey} in the config or passed as a parameter`);
  }
}
