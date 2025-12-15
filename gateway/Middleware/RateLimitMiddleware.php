<?php

namespace Gateway\Middleware;

use Gateway\Core\Response;

/**
 * Rate Limiting Middleware
 * Prevents API abuse by limiting requests per IP
 */
class RateLimitMiddleware
{
    private $maxRequests = 100; // Maximum requests per window
    private $windowSeconds = 60; // Time window in seconds
    private $cacheDir;

    public function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../storage/rate_limits';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function handle()
    {
        // Get client IP
        $ip = $this->getClientIp();
        
        // Check rate limit
        if (!$this->checkRateLimit($ip)) {
            Response::error('Too many requests. Please try again later.', 429);
        }
    }

    /**
     * Check if the IP has exceeded the rate limit
     */
    private function checkRateLimit($ip)
    {
        $key = md5($ip);
        $file = $this->cacheDir . '/' . $key . '.json';

        $now = time();
        $data = $this->loadRateLimitData($file);

        // Clean old entries
        $data = array_filter($data, function($timestamp) use ($now) {
            return ($now - $timestamp) < $this->windowSeconds;
        });

        // Check if limit exceeded
        if (count($data) >= $this->maxRequests) {
            return false;
        }

        // Add current request
        $data[] = $now;

        // Save updated data
        file_put_contents($file, json_encode($data));

        return true;
    }

    /**
     * Load rate limit data from file
     */
    private function loadRateLimitData($file)
    {
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Get client IP address
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
}
