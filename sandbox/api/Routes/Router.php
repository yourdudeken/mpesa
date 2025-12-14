<?php

namespace Yourdudeken\Mpesa\Api\Routes;

class Router
{
    private $routes = [];
    private $prefix = '';

    /**
     * Set route prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = rtrim($prefix, '/');
        return $this;
    }

    /**
     * Add GET route
     */
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }

    /**
     * Add POST route
     */
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }

    /**
     * Add PUT route
     */
    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
        return $this;
    }

    /**
     * Add DELETE route
     */
    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
        return $this;
    }

    /**
     * Add route for any method
     */
    public function any($path, $handler)
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
        return $this;
    }

    /**
     * Add route
     */
    private function addRoute($method, $path, $handler)
    {
        $fullPath = $this->prefix . '/' . ltrim($path, '/');
        $this->routes[$method][$fullPath] = $handler;
    }

    /**
     * Dispatch request
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash except for root
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        // Check if route exists
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            return $this->executeHandler($handler);
        }

        // Route not found
        $this->sendNotFound();
    }

    /**
     * Execute handler
     */
    private function executeHandler($handler)
    {
        if (is_callable($handler)) {
            return call_user_func($handler);
        }

        if (is_array($handler) && count($handler) === 2) {
            list($controller, $method) = $handler;
            
            if (is_string($controller)) {
                $controller = new $controller();
            }

            if (method_exists($controller, $method)) {
                return call_user_func([$controller, $method]);
            }
        }

        $this->sendError('Invalid route handler');
    }

    /**
     * Send 404 response
     */
    private function sendNotFound()
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Endpoint not found',
            ],
            'timestamp' => date('c'),
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send error response
     */
    private function sendError($message)
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => $message,
            ],
            'timestamp' => date('c'),
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Get all registered routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
