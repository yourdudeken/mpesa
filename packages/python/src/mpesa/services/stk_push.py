from typing import Optional
from .http.http_client import HttpClient
from .auth import Auth
from .utils import Helpers


class STKPushService:
    def __init__(self, http_client: HttpClient, auth: Auth, config):
        self.http_client = http_client
        self.auth = auth
        self.helpers = Helpers(config)
        self.base_url = http_client.get_base_url()

    def push(self, phonenumber: str, amount: int, account_number: str, 
             callback_url: Optional[str] = None, transaction_type: str = 'CustomerPayBillOnline',
             short_code_type: str = 'C2B') -> dict:
        if not account_number:
            raise ValueError('An Account Reference is required for All transactions.')
        
        if transaction_type == 'CustomerBuyGoodsOnline' and not self.helpers.get_config('till_number'):
            raise ValueError('Till number is required for Buy Goods transactions.')
        
        url = f'{self.base_url}/mpesa/stkpush/v1/processrequest'
        
        data = {
            'BusinessShortCode': self.helpers.get_config('shortcode'),
            'Password': self.helpers.lipa_na_mpesa_password(),
            'Timestamp': self.helpers.get_formatted_timestamp(),
            'Amount': amount,
            'PartyA': self.helpers.phone_validator(phonenumber),
            'PartyB': self.helpers.get_config('shortcode') if transaction_type == 'CustomerPayBillOnline' else self.helpers.get_config('till_number'),
            'TransactionType': transaction_type,
            'PhoneNumber': self.helpers.phone_validator(phonenumber),
            'TransactionDesc': 'Payment',
            'AccountReference': account_number,
            'CallBackURL': callback_url or self.helpers.get_config('callbacks.callback_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, data, token)

    def query(self, checkout_request_id: str, short_code_type: str = 'C2B') -> dict:
        url = f'{self.base_url}/mpesa/stkpushquery/v1/query'
        
        data = {
            'BusinessShortCode': self.helpers.get_config('shortcode'),
            'Password': self.helpers.lipa_na_mpesa_password(),
            'Timestamp': self.helpers.get_formatted_timestamp(),
            'CheckoutRequestID': checkout_request_id,
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, data, token)
