<?php
/**
 * M-Pesa API Gateway Entry Point
 * 
 * This is the main entry point for the M-Pesa API Gateway
 * All API requests are routed through this file
 * 
 * @author Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Gateway\Core\Router;
use Gateway\Core\Response;
use Gateway\Middleware\CorsMiddleware;
use Gateway\Middleware\AuthMiddleware;
use Gateway\Middleware\RateLimitMiddleware;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? false);

// Set timezone
date_default_timezone_set('Africa/Nairobi');

// Set headers for JSON API
header('Content-Type: application/json');

// Handle CORS
CorsMiddleware::handle();

// Initialize Router
$router = new Router();

// Apply global middleware
$router->addMiddleware(new RateLimitMiddleware());

// Health check endpoint (no auth required)
$router->get('/health', 'Gateway\Controllers\HealthController@check');
$router->get('/api/v1/health', 'Gateway\Controllers\HealthController@check');

// API Documentation
$router->get('/api/v1/docs', 'Gateway\Controllers\DocsController@index');

// Protected routes - require API key authentication
$router->group(['middleware' => [AuthMiddleware::class]], function($router) {
    
    // STK Push (Lipa Na M-Pesa Online)
    $router->post('/api/v1/stkpush', 'Gateway\Controllers\STKPushController@initiate');
    $router->post('/api/v1/stkpush/query', 'Gateway\Controllers\STKPushController@query');
    
    // C2B (Customer to Business)
    $router->post('/api/v1/c2b/register', 'Gateway\Controllers\C2BController@register');
    $router->post('/api/v1/c2b/simulate', 'Gateway\Controllers\C2BController@simulate');
    
    // B2C (Business to Customer)
    $router->post('/api/v1/b2c/payment', 'Gateway\Controllers\B2CController@payment');
    
    // B2B (Business to Business)
    $router->post('/api/v1/b2b/payment', 'Gateway\Controllers\B2BController@payment');
    
    // Account Balance
    $router->post('/api/v1/account/balance', 'Gateway\Controllers\AccountController@balance');
    
    // Transaction Status
    $router->post('/api/v1/transaction/status', 'Gateway\Controllers\TransactionController@status');
    
    // Reversal
    $router->post('/api/v1/transaction/reversal', 'Gateway\Controllers\TransactionController@reversal');
    
    // Transaction History
    $router->get('/api/v1/transactions', 'Gateway\Controllers\TransactionController@history');
    $router->get('/api/v1/transactions/{id}', 'Gateway\Controllers\TransactionController@show');
});

// Callback endpoints (no auth required - validated by M-Pesa)
$router->post('/api/v1/callbacks/stkpush', 'Gateway\Controllers\CallbackController@stkpush');
$router->post('/api/v1/callbacks/c2b/validation', 'Gateway\Controllers\CallbackController@c2bValidation');
$router->post('/api/v1/callbacks/c2b/confirmation', 'Gateway\Controllers\CallbackController@c2bConfirmation');
$router->post('/api/v1/callbacks/b2c/result', 'Gateway\Controllers\CallbackController@b2cResult');
$router->post('/api/v1/callbacks/b2c/timeout', 'Gateway\Controllers\CallbackController@b2cTimeout');
$router->post('/api/v1/callbacks/b2b/result', 'Gateway\Controllers\CallbackController@b2bResult');
$router->post('/api/v1/callbacks/b2b/timeout', 'Gateway\Controllers\CallbackController@b2bTimeout');
$router->post('/api/v1/callbacks/balance/result', 'Gateway\Controllers\CallbackController@balanceResult');
$router->post('/api/v1/callbacks/balance/timeout', 'Gateway\Controllers\CallbackController@balanceTimeout');
$router->post('/api/v1/callbacks/reversal/result', 'Gateway\Controllers\CallbackController@reversalResult');
$router->post('/api/v1/callbacks/reversal/timeout', 'Gateway\Controllers\CallbackController@reversalTimeout');
$router->post('/api/v1/callbacks/status/result', 'Gateway\Controllers\CallbackController@statusResult');
$router->post('/api/v1/callbacks/status/timeout', 'Gateway\Controllers\CallbackController@statusTimeout');

// Dispatch the request
try {
    $router->dispatch();
} catch (\Exception $e) {
    Response::error($e->getMessage(), $e->getCode() ?: 500);
}
