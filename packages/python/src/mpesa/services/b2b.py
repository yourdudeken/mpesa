from typing import Optional
from .http.http_client import HttpClient
from .auth import Auth
from .utils import Helpers


class B2BService:
    def __init__(self, http_client: HttpClient, auth: Auth, config):
        self.http_client = http_client
        self.auth = auth
        self.helpers = Helpers(config)
        self.base_url = http_client.get_base_url()

    def send(self, receiver_shortcode: str, command_id: str, amount: int, remarks: str,
             account_number: Optional[str] = None, result_url: Optional[str] = None,
             timeout_url: Optional[str] = None, short_code_type: str = 'B2B') -> dict:
        if command_id == 'BusinessPayBill' and not account_number:
            raise ValueError('Account Number is required for BusinessPayBill CommandID')
        
        url = f'{self.base_url}/mpesa/b2b/v1/paymentrequest'
        
        body = {
            'Initiator': self.helpers.get_config('initiator_name'),
            'SecurityCredential': self.helpers.generate_security_credential(),
            'CommandID': command_id,
            'SenderIdentifierType': '4',
            'RecieverIdentifierType': '4',
            'Amount': amount,
            'PartyA': self.helpers.get_config('b2c_shortcode'),
            'PartyB': receiver_shortcode,
            'AccountReference': account_number,
            'Remarks': remarks,
            'ResultURL': result_url or self.helpers.get_config('callbacks.b2b_result_url'),
            'QueueTimeOutURL': timeout_url or self.helpers.get_config('callbacks.b2b_timeout_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, body, token)
