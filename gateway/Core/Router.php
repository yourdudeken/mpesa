<?php

namespace Gateway\Core;

/**
 * Router Class
 * Handles HTTP routing for the API Gateway
 */
class Router
{
    private $routes = [];
    private $middleware = [];
    private $groupMiddleware = [];

    /**
     * Add a GET route
     */
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add a POST route
     */
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add a PUT route
     */
    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Add a DELETE route
     */
    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add a route
     */
    private function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $this->groupMiddleware
        ];
    }

    /**
     * Add global middleware
     */
    public function addMiddleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Create a route group with middleware
     */
    public function group($options, $callback)
    {
        $previousMiddleware = $this->groupMiddleware;
        
        if (isset($options['middleware'])) {
            $this->groupMiddleware = array_merge(
                $this->groupMiddleware,
                $options['middleware']
            );
        }

        $callback($this);

        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Dispatch the request to the appropriate handler
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove trailing slash
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        // Find matching route
        $route = $this->findRoute($method, $path);

        if (!$route) {
            Response::error('Route not found', 404);
            return;
        }

        // Execute global middleware
        foreach ($this->middleware as $middleware) {
            if (is_object($middleware)) {
                $middleware->handle();
            }
        }

        // Execute route-specific middleware
        if (!empty($route['middleware'])) {
            foreach ($route['middleware'] as $middlewareClass) {
                $middleware = new $middlewareClass();
                $middleware->handle();
            }
        }

        // Execute the handler
        $this->executeHandler($route['handler'], $route['params'] ?? []);
    }

    /**
     * Find a matching route
     */
    private function findRoute($method, $path)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert route pattern to regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $route['params'] = $params;
                return $route;
            }
        }

        return null;
    }

    /**
     * Execute the route handler
     */
    private function executeHandler($handler, $params = [])
    {
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            
            if (!class_exists($controller)) {
                Response::error("Controller $controller not found", 500);
                return;
            }

            $controllerInstance = new $controller();
            
            if (!method_exists($controllerInstance, $method)) {
                Response::error("Method $method not found in controller $controller", 500);
                return;
            }

            $controllerInstance->$method($params);
        } elseif (is_callable($handler)) {
            $handler($params);
        }
    }
}
