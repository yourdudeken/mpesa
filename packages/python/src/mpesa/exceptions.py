class MpesaException(Exception):
    def __init__(self, message: str, code: int = 0):
        super().__init__(message)
        self.message = message
        self.code = code

    @staticmethod
    def invalid_transaction_type(transaction_type: str):
        return MpesaException(f"Invalid transaction type: {transaction_type}. Use PAYBILL or TILL.")

    @staticmethod
    def missing_account_reference():
        return MpesaException('An Account Reference is required for All transactions.')

    @staticmethod
    def missing_till_number():
        return MpesaException('Till number is required for Buy Goods transactions.')

    @staticmethod
    def missing_callback_url(key: str):
        return MpesaException(f"Ensure you have set the {key} in the config or passed as a parameter")

    @staticmethod
    def missing_b2b_account_number():
        return MpesaException('Account Number is required for BusinessPayBill CommandID')

    @staticmethod
    def authentication_failed(message: str = 'Authentication failed'):
        return MpesaException(message, 401)

    @staticmethod
    def api_error(message: str, code: int = 0):
        return MpesaException(message, code)
