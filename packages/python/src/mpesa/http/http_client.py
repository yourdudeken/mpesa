import requests
from typing import Dict, Any


class HttpClient:
    def __init__(self, base_url: str):
        self.base_url = base_url

    def post(self, url: str, body: Dict[str, Any], token: str) -> Dict[str, Any]:
        headers = {
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json',
        }
        
        response = requests.post(url, json=body, headers=headers)
        response.raise_for_status()
        
        return response.json()

    def get(self, url: str, token: str) -> Dict[str, Any]:
        headers = {
            'Authorization': f'Bearer {token}',
        }
        
        response = requests.get(url, headers=headers)
        response.raise_for_status()
        
        return response.json()

    def get_base_url(self) -> str:
        return self.base_url
