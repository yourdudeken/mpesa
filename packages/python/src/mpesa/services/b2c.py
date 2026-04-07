from typing import Optional
from .http.http_client import HttpClient
from .auth import Auth
from .utils import Helpers


class B2CService:
    def __init__(self, http_client: HttpClient, auth: Auth, config):
        self.http_client = http_client
        self.auth = auth
        self.helpers = Helpers(config)
        self.base_url = http_client.get_base_url()

    def send(self, phonenumber: str, command_id: str, amount: int, remarks: str,
             result_url: Optional[str] = None, timeout_url: Optional[str] = None,
             short_code_type: str = 'B2C') -> dict:
        url = f'{self.base_url}/mpesa/b2c/v1/paymentrequest'
        
        body = {
            'InitiatorName': self.helpers.get_config('initiator_name'),
            'SecurityCredential': self.helpers.generate_security_credential(),
            'CommandID': command_id,
            'Amount': amount,
            'PartyA': self.helpers.get_config('b2c_shortcode'),
            'PartyB': self.helpers.phone_validator(phonenumber),
            'Remarks': remarks,
            'Occassion': '',
            'ResultURL': result_url or self.helpers.get_config('callbacks.b2c_result_url'),
            'QueueTimeOutURL': timeout_url or self.helpers.get_config('callbacks.b2c_timeout_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, body, token)

    def validated(self, phonenumber: str, command_id: str, amount: int, remarks: str,
                  id_number: str, result_url: Optional[str] = None, timeout_url: Optional[str] = None,
                  short_code_type: str = 'B2C') -> dict:
        url = f'{self.base_url}/mpesa/b2cvalidate/v2/paymentrequest'
        
        body = {
            'InitiatorName': self.helpers.get_config('initiator_name'),
            'SecurityCredential': self.helpers.generate_security_credential(),
            'CommandID': command_id,
            'Amount': amount,
            'PartyA': self.helpers.get_config('b2c_shortcode'),
            'PartyB': self.helpers.phone_validator(phonenumber),
            'Remarks': remarks,
            'Occassion': '',
            'OriginatorConversationID': self.helpers.get_formatted_timestamp(),
            'IDType': '01',
            'IDNumber': id_number,
            'ResultURL': result_url or self.helpers.get_config('callbacks.b2c_result_url'),
            'QueueTimeOutURL': timeout_url or self.helpers.get_config('callbacks.b2c_timeout_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, body, token)
