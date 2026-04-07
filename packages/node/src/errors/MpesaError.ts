export class MpesaError extends Error {
  constructor(message: string, public code?: number) {
    super(message);
    this.name = 'MpesaError';
  }

  static invalidTransactionType(type: string): MpesaError {
    return new MpesaError(`Invalid transaction type: ${type}. Use PAYBILL or TILL.`);
  }

  static missingAccountReference(): MpesaError {
    return new MpesaError('An Account Reference is required for All transactions.');
  }

  static missingTillNumber(): MpesaError {
    return new MpesaError('Till number is required for Buy Goods transactions.');
  }

  static missingCallbackUrl(key: string): MpesaError {
    return new MpesaError(`Ensure you have set the ${key} in the config or passed as a parameter`);
  }

  static missingB2BAccountNumber(): MpesaError {
    return new MpesaError('Account Number is required for BusinessPayBill CommandID');
  }

  static authenticationFailed(message: string = 'Authentication failed'): MpesaError {
    return new MpesaError(message, 401);
  }

  static apiError(message: string, code: number = 0): MpesaError {
    return new MpesaError(message, code);
  }
}
