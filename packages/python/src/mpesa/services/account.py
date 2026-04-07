from typing import Optional
from .http.http_client import HttpClient
from .auth import Auth
from .utils import Helpers


class AccountService:
    def __init__(self, http_client: HttpClient, auth: Auth, config):
        self.http_client = http_client
        self.auth = auth
        self.helpers = Helpers(config)
        self.base_url = http_client.get_base_url()

    def balance(self, shortcode: str, identifier_type: int, remarks: str,
                result_url: Optional[str] = None, timeout_url: Optional[str] = None,
                short_code_type: str = 'C2B') -> dict:
        url = f'{self.base_url}/mpesa/accountbalance/v1/query'
        
        body = {
            'Initiator': self.helpers.get_config('initiator_name'),
            'SecurityCredential': self.helpers.generate_security_credential(),
            'CommandID': 'AccountBalance',
            'PartyA': shortcode,
            'IdentifierType': identifier_type,
            'Remarks': remarks,
            'ResultURL': result_url or self.helpers.get_config('callbacks.balance_result_url'),
            'QueueTimeOutURL': timeout_url or self.helpers.get_config('callbacks.balance_timeout_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, body, token)

    def status(self, shortcode: str, transaction_id: str, identifier_type: int, remarks: str,
              result_url: Optional[str] = None, timeout_url: Optional[str] = None,
              short_code_type: str = 'C2B') -> dict:
        url = f'{self.base_url}/mpesa/transactionstatus/v1/query'
        
        body = {
            'Initiator': self.helpers.get_config('initiator_name'),
            'SecurityCredential': self.helpers.generate_security_credential(),
            'CommandID': 'TransactionStatusQuery',
            'TransactionID': transaction_id,
            'PartyA': shortcode,
            'IdentifierType': identifier_type,
            'Remarks': remarks,
            'Occassion': '',
            'ResultURL': result_url or self.helpers.get_config('callbacks.status_result_url'),
            'QueueTimeOutURL': timeout_url or self.helpers.get_config('callbacks.status_timeout_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, body, token)

    def reversal(self, shortcode: str, transaction_id: str, amount: float, remarks: str,
                 result_url: Optional[str] = None, timeout_url: Optional[str] = None,
                 short_code_type: str = 'C2B') -> dict:
        url = f'{self.base_url}/mpesa/reversal/v1/request'
        
        body = {
            'Initiator': self.helpers.get_config('initiator_name'),
            'SecurityCredential': self.helpers.generate_security_credential(),
            'CommandID': 'TransactionReversal',
            'TransactionID': transaction_id,
            'Amount': amount,
            'ReceiverParty': shortcode,
            'RecieverIdentifierType': '11',
            'Remarks': remarks,
            'Occasion': '',
            'ResultURL': result_url or self.helpers.get_config('callbacks.reversal_result_url'),
            'QueueTimeOutURL': timeout_url or self.helpers.get_config('callbacks.reversal_timeout_url'),
        }
        
        token = self.auth.get_access_token(short_code_type)
        return self.http_client.post(url, body, token)
