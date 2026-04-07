import time
import requests
from requests.auth import HTTPBasicAuth
from typing import Optional


class Auth:
    def __init__(self, config):
        self.config = config
        self.base_url = 'https://sandbox.safaricom.co.ke' if config.environment == 'sandbox' else 'https://api.safaricom.co.ke'
        self._access_token: Optional[str] = None
        self._token_expiry: float = 0

    def get_access_token(self, short_code_type: str = 'C2B') -> str:
        if self._access_token and time.time() < self._token_expiry:
            return self._access_token
        
        consumer_key = self.config.b2c_consumer_key if short_code_type in ['B2C', 'B2B'] else self.config.mpesa_consumer_key
        consumer_secret = self.config.b2c_consumer_secret if short_code_type in ['B2C', 'B2B'] else self.config.mpesa_consumer_secret
        
        url = f'{self.base_url}/oauth/v1/generate?grant_type=client_credentials'
        
        response = requests.get(url, auth=HTTPBasicAuth(consumer_key, consumer_secret))
        response.raise_for_status()
        
        data = response.json()
        self._access_token = data.get('access_token')
        self._token_expiry = time.time() + (data.get('expires_in', 3600) - 60)
        
        return self._access_token

    def clear_token(self):
        self._access_token = None
        self._token_expiry = 0
