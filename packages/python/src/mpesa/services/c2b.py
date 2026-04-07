from typing import Optional
from .http.http_client import HttpClient
from .auth import Auth
from .utils import Helpers


class C2BService:
    def __init__(self, http_client: HttpClient, auth: Auth, config):
        self.http_client = http_client
        self.auth = auth
        self.helpers = Helpers(config)
        self.base_url = http_client.get_base_url()

    def register_urls(self, shortcode: str, confirm_url: Optional[str] = None,
                     validate_url: Optional[str] = None, short_code_type: str = 'C2B') -> dict:
        url = f'{self.base_url}/mpesa/c2b/v2/registerurl'
        
        body = {
            'ShortCode': shortcode,
            'ResponseType': 'Completed',
            'ConfirmationURL': confirm_url or self.helpers.get_config('callbacks.c2b_confirmation_url'),
            'ValidationURL': validate_url or self.helpers.get_config('callbacks.c2b_validation_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, body, token)

    def simulate(self, phonenumber: str, amount: int, shortcode: str, command_id: str,
                 account_number: Optional[str] = None, short_code_type: str = 'C2B') -> dict:
        url = f'{self.base_url}/mpesa/c2b/v2/simulate'
        
        if command_id == 'CustomerPayBillOnline':
            data = {
                'Msisdn': self.helpers.phone_validator(phonenumber),
                'Amount': amount,
                'BillRefNumber': account_number,
                'CommandID': command_id,
                'ShortCode': shortcode,
            }
        else:
            data = {
                'Msisdn': self.helpers.phone_validator(phonenumber),
                'Amount': amount,
                'CommandID': command_id,
                'ShortCode': shortcode,
            }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, data, token)
