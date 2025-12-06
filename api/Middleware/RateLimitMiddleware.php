<?php

namespace Yourdudeken\Mpesa\Api\Middleware;

class RateLimitMiddleware
{
    private $config;
    private $storageDir;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->storageDir = $config['storage'];
        
        // Create storage directory if it doesn't exist
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Check rate limit for the given API key
     * 
     * @param string $apiKey
     * @param int $limit Requests per minute
     * @return bool
     */
    public function checkLimit($apiKey, $limit = null)
    {
        if (!$this->config['enabled']) {
            return true;
        }

        $limit = $limit ?? $this->config['default_limit'];
        $key = md5($apiKey);
        $file = $this->storageDir . $key . '.json';
        
        $now = time();
        $windowStart = $now - 60; // 1 minute window

        // Load existing requests
        $requests = $this->loadRequests($file);
        
        // Filter requests within the current window
        $requests = array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });

        // Add current request
        $requests[] = $now;
        
        // Save requests
        $this->saveRequests($file, $requests);

        // Check if limit exceeded
        $requestCount = count($requests);
        $remaining = max(0, $limit - $requestCount);

        // Set rate limit headers
        header("X-RateLimit-Limit: $limit");
        header("X-RateLimit-Remaining: $remaining");
        header("X-RateLimit-Reset: " . ($now + 60));

        if ($requestCount > $limit) {
            $this->sendRateLimitResponse($limit);
            return false;
        }

        return true;
    }

    /**
     * Load requests from file
     */
    private function loadRequests($file)
    {
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);
        
        return is_array($data) ? $data : [];
    }

    /**
     * Save requests to file
     */
    private function saveRequests($file, $requests)
    {
        file_put_contents($file, json_encode($requests));
    }

    /**
     * Send rate limit exceeded response
     */
    private function sendRateLimitResponse($limit)
    {
        http_response_code(429);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'message' => "Rate limit exceeded. Maximum $limit requests per minute allowed.",
            ],
            'timestamp' => date('c'),
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
