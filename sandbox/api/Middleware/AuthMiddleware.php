<?php

namespace Yourdudeken\Mpesa\Api\Middleware;

class AuthMiddleware
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Authenticate the request using API key
     * 
     * @return array|null Returns client info if authenticated, null otherwise
     */
    public function authenticate()
    {
        $apiKey = $this->getApiKey();

        if (!$apiKey) {
            $this->sendUnauthorizedResponse('API key is required');
            return null;
        }

        // Validate API key
        if (!isset($this->config['api_keys'][$apiKey])) {
            $this->sendUnauthorizedResponse('Invalid API key');
            return null;
        }

        $client = $this->config['api_keys'][$apiKey];

        // Check if API key is active
        if (!$client['active']) {
            $this->sendUnauthorizedResponse('API key is inactive');
            return null;
        }

        return $client;
    }

    /**
     * Get API key from request headers
     */
    private function getApiKey()
    {
        // Check X-API-Key header
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }

        // Check Authorization header (Bearer token)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        // Check query parameter (not recommended for production)
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        return null;
    }

    /**
     * Send unauthorized response
     */
    private function sendUnauthorizedResponse($message)
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => $message,
            ],
            'timestamp' => date('c'),
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
