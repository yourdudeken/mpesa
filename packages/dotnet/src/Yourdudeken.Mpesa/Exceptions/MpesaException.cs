using System;

namespace Yourdudeken.Mpesa.Exceptions
{
    public class MpesaException : Exception
    {
        public int Code { get; }

        public MpesaException(string message) : base(message) { }

        public MpesaException(string message, int code) : base(message)
        {
            Code = code;
        }

        public static MpesaException InvalidTransactionType(string type) =>
            new MpesaException($"Invalid transaction type: {type}. Use PAYBILL or TILL.");

        public static MpesaException MissingAccountReference() =>
            new MpesaException("An Account Reference is required for All transactions.");

        public static MpesaException MissingTillNumber() =>
            new MpesaException("Till number is required for Buy Goods transactions.");

        public static MpesaException MissingCallbackUrl(string key) =>
            new MpesaException($"Ensure you have set the {key} in the config or passed as a parameter");

        public static MpesaException MissingB2BAccountNumber() =>
            new MpesaException("Account Number is required for BusinessPayBill CommandID");

        public static MpesaException AuthenticationFailed(string message = "Authentication failed") =>
            new MpesaException(message, 401);

        public static MpesaException ApiError(string message, int code = 0) =>
            new MpesaException(message, code);
    }
}
