import base64
import os
from datetime import datetime
from typing import Optional, Dict, Any
from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric import padding
from cryptography.hazmat.backends import default_backend


class Helpers:
    def __init__(self, config):
        self.config = config

    def get_config(self, key: str, default=None):
        return getattr(self.config, key, default)

    def phone_validator(self, phone_no: str) -> str:
        phone_no = phone_no.lstrip('+')
        if phone_no.startswith('0'):
            phone_no = '254' + phone_no[1:]
        elif phone_no.startswith('7'):
            phone_no = '254' + phone_no
        return phone_no

    def get_formatted_timestamp(self) -> str:
        return datetime.now().strftime('%Y%m%d%H%M%S')

    def lipa_na_mpesa_password(self) -> str:
        timestamp = self.get_formatted_timestamp()
        password = str(self.get_config('shortcode')) + self.get_config('passkey') + timestamp
        return base64.b64encode(password.encode()).decode()

    def generate_security_credential(self) -> str:
        cert_path = os.path.join(os.path.dirname(__file__), '..', 'certificates', 
                                'SandboxCertificate.cer' if self.get_config('environment') == 'sandbox' else 'ProductionCertificate.cer')
        
        with open(cert_path, 'rb') as f:
            pubkey = f.read()
        
        public_key = serialization.load_pem_public_key(pubkey, default_backend())
        
        password = self.get_config('initiator_password').encode()
        encrypted = public_key.encrypt(password, padding.PKCS1v15())
        
        return base64.b64encode(encrypted).decode()

    def resolve_callback_url(self, param_url: Optional[str], config_key: str) -> str:
        config_url = self.config.callbacks.get(config_key) if self.config.callbacks else None
        if param_url:
            return param_url
        if config_url:
            return config_url
        raise ValueError(f"Ensure you have set the {config_key} in the config or passed as a parameter")
