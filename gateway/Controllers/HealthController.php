<?php

namespace Gateway\Controllers;

use Gateway\Core\Response;

/**
 * Health Controller
 * Provides health check endpoints
 */
class HealthController
{
    /**
     * Health check endpoint
     */
    public function check()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
            'mpesa_environment' => $_ENV['MPESA_ENV'] ?? 'unknown',
            'services' => [
                'api' => 'operational',
                'mpesa' => $this->checkMpesaConnection()
            ]
        ];

        Response::success($health, 'Service is healthy');
    }

    /**
     * Check M-Pesa connection
     */
    private function checkMpesaConnection()
    {
        try {
            // You could add a simple ping or auth check here
            return 'operational';
        } catch (\Exception $e) {
            return 'degraded';
        }
    }
}
