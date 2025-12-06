<?php

/**
 * M-Pesa API Wrapper
 * 
 * A RESTful API wrapper for the M-Pesa library with API key authentication and CORS support
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable for production

// Autoload
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/autoload.php';

// Load API classes
spl_autoload_register(function ($class) {
    $prefix = 'Yourdudeken\Mpesa\Api\\';
    $baseDir = __DIR__ . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use Yourdudeken\Mpesa\Api\Routes\Router;
use Yourdudeken\Mpesa\Api\Middleware\CorsMiddleware;
use Yourdudeken\Mpesa\Api\Middleware\AuthMiddleware;
use Yourdudeken\Mpesa\Api\Middleware\RateLimitMiddleware;
use Yourdudeken\Mpesa\Api\Controllers\MpesaController;

// Load configuration
$config = require __DIR__ . '/Config/api.php';

// Initialize middleware
$corsMiddleware = new CorsMiddleware($config['cors']);
$authMiddleware = new AuthMiddleware($config);
$rateLimitMiddleware = new RateLimitMiddleware($config['rate_limit']);

// Handle CORS
$corsMiddleware->handle();

// Get current path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Skip authentication for health check
if ($path !== '/api/health') {
    // Authenticate request
    $client = $authMiddleware->authenticate();
    
    if ($client) {
        // Check rate limit
        $limit = $client['rate_limit'] ?? $config['rate_limit']['default_limit'];
        $rateLimitMiddleware->checkLimit($_SERVER['HTTP_X_API_KEY'] ?? '', $limit);
    }
}

// Initialize router
$router = new Router();
$router->setPrefix('/api');

// Initialize controller with config
$mpesaController = new MpesaController($config['response']);

// Define routes
$router->get('/health', [$mpesaController, 'health']);
$router->post('/stk-push', [$mpesaController, 'stkPush']);
$router->post('/stk-query', [$mpesaController, 'stkQuery']);
$router->post('/b2c', [$mpesaController, 'b2c']);
$router->post('/b2b', [$mpesaController, 'b2b']);
$router->post('/c2b/register', [$mpesaController, 'c2bRegister']);
$router->post('/c2b/simulate', [$mpesaController, 'c2bSimulate']);
$router->post('/balance', [$mpesaController, 'balance']);
$router->post('/transaction-status', [$mpesaController, 'transactionStatus']);
$router->post('/reversal', [$mpesaController, 'reversal']);

// Dispatch request
try {
    $router->dispatch();
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INTERNAL_ERROR',
            'message' => 'An internal error occurred',
            'details' => $e->getMessage(),
        ],
        'timestamp' => date('c'),
    ], JSON_PRETTY_PRINT);
}
