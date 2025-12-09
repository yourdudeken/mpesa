<?php

namespace Yourdudeken\Mpesa\Api\Middleware;

class CorsMiddleware
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Handle CORS preflight and add CORS headers
     */
    public function handle()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Check if origin is allowed
        if ($this->isOriginAllowed($origin)) {
            header("Access-Control-Allow-Origin: " . ($this->config['allowed_origins'][0] === '*' ? '*' : $origin));
        }

        // Set CORS headers
        header("Access-Control-Allow-Methods: " . implode(', ', $this->config['allowed_methods']));
        header("Access-Control-Allow-Headers: " . implode(', ', $this->config['allowed_headers']));
        header("Access-Control-Expose-Headers: " . implode(', ', $this->config['exposed_headers']));
        header("Access-Control-Max-Age: " . $this->config['max_age']);

        if ($this->config['supports_credentials']) {
            header("Access-Control-Allow-Credentials: true");
        }

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * Check if the origin is allowed
     */
    private function isOriginAllowed($origin)
    {
        if (empty($origin)) {
            return false;
        }

        // Allow all origins
        if (in_array('*', $this->config['allowed_origins'])) {
            return true;
        }

        // Check specific origins
        return in_array($origin, $this->config['allowed_origins']);
    }
}
