package com.yourdudeken.mpesa.exceptions;

public class MpesaException extends Exception {
    private int code;

    public MpesaException(String message) {
        super(message);
    }

    public MpesaException(String message, int code) {
        super(message);
        this.code = code;
    }

    public int getCode() {
        return code;
    }

    public static MpesaException invalidTransactionType(String type) {
        return new MpesaException("Invalid transaction type: " + type + ". Use PAYBILL or TILL.");
    }

    public static MpesaException missingAccountReference() {
        return new MpesaException("An Account Reference is required for All transactions.");
    }

    public static MpesaException missingTillNumber() {
        return new MpesaException("Till number is required for Buy Goods transactions.");
    }

    public static MpesaException missingCallbackUrl(String key) {
        return new MpesaException("Ensure you have set the " + key + " in the config or passed as a parameter");
    }

    public static MpesaException missingB2BAccountNumber() {
        return new MpesaException("Account Number is required for BusinessPayBill CommandID");
    }

    public static MpesaException authenticationFailed(String message) {
        return new MpesaException(message, 401);
    }

    public static MpesaException apiError(String message, int code) {
        return new MpesaException(message, code);
    }
}
