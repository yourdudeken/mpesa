<?php

namespace Gateway\Middleware;

use Gateway\Core\Response;

/**
 * Authentication Middleware
 * Validates API keys for protected routes
 */
class AuthMiddleware
{
    public function handle()
    {
        $apiKey = $this->getApiKey();

        if (!$apiKey) {
            Response::unauthorized('API key is required. Please provide an API key in the Authorization header or X-API-Key header.');
        }

        if (!$this->validateApiKey($apiKey)) {
            Response::unauthorized('Invalid API key');
        }

        // Store the validated API key for later use
        $_SERVER['VALIDATED_API_KEY'] = $apiKey;
    }

    /**
     * Get API key from request headers
     */
    private function getApiKey()
    {
        // Check Authorization header (Bearer token)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        // Check X-API-Key header
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }

        return null;
    }

    /**
     * Validate the API key
     */
    private function validateApiKey($apiKey)
    {
        // In production, this should check against a database
        // For now, we'll use environment variable or a simple check
        $validKeys = $this->getValidApiKeys();

        return in_array($apiKey, $validKeys);
    }

    /**
     * Get valid API keys
     */
    private function getValidApiKeys()
    {
        $keys = [];

        // Get from environment variable
        if (!empty($_ENV['API_KEYS'])) {
            $keys = explode(',', $_ENV['API_KEYS']);
        }

        // Default key for development (should be removed in production)
        if ($_ENV['APP_ENV'] === 'local' || $_ENV['APP_ENV'] === 'development') {
            $keys[] = 'dev_api_key_12345';
        }

        // You can also load from database here
        // $keys = array_merge($keys, $this->loadKeysFromDatabase());

        return array_map('trim', $keys);
    }

    /**
     * Load API keys from database (implement as needed)
     */
    private function loadKeysFromDatabase()
    {
        // TODO: Implement database lookup
        // Example:
        // $db = new Database();
        // return $db->query("SELECT api_key FROM api_keys WHERE active = 1")->fetchAll();
        
        return [];
    }
}
