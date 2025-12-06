<?php

namespace Yourdudeken\Mpesa\Api\Controllers;

class BaseController
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Send JSON response
     */
    protected function sendResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($this->config['include_timestamp'] ?? true) {
            $response['timestamp'] = date('c');
        }

        if ($this->config['include_request_id'] ?? true) {
            $response['request_id'] = $this->generateRequestId();
        }

        $options = JSON_UNESCAPED_SLASHES;
        if ($this->config['pretty_print'] ?? true) {
            $options |= JSON_PRETTY_PRINT;
        }

        echo json_encode($response, $options);
        exit;
    }

    /**
     * Send error response
     */
    protected function sendError($message, $code = 'ERROR', $statusCode = 400, $details = null)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ];

        if ($details !== null) {
            $response['error']['details'] = $details;
        }

        if ($this->config['include_timestamp'] ?? true) {
            $response['timestamp'] = date('c');
        }

        if ($this->config['include_request_id'] ?? true) {
            $response['request_id'] = $this->generateRequestId();
        }

        $options = JSON_UNESCAPED_SLASHES;
        if ($this->config['pretty_print'] ?? true) {
            $options |= JSON_PRETTY_PRINT;
        }

        echo json_encode($response, $options);
        exit;
    }

    /**
     * Get JSON input from request body
     */
    protected function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError('Invalid JSON input: ' . json_last_error_msg(), 'INVALID_JSON', 400);
        }

        return $data ?? [];
    }

    /**
     * Validate required fields
     */
    protected function validateRequired($data, $requiredFields)
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->sendError(
                'Missing required fields',
                'VALIDATION_ERROR',
                400,
                ['missing_fields' => $missing]
            );
        }
    }

    /**
     * Generate unique request ID
     */
    private function generateRequestId()
    {
        return uniqid('req_', true);
    }

    /**
     * Log API request/response
     */
    protected function log($message, $level = 'info', $context = [])
    {
        if (!($this->config['logging']['enabled'] ?? false)) {
            return;
        }

        $logFile = $this->config['logging']['log_file'] ?? null;
        if (!$logFile) {
            return;
        }

        // Create log directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message $contextStr" . PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
