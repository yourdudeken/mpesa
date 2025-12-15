<?php

namespace Gateway\Core;

/**
 * Request Class
 * Handles incoming HTTP requests
 */
class Request
{
    private $data = [];
    private $headers = [];

    public function __construct()
    {
        $this->parseRequest();
        $this->parseHeaders();
    }

    /**
     * Parse the incoming request
     */
    private function parseRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->data = $_GET;
        } elseif ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $this->data = json_decode($json, true) ?? [];
            } else {
                $this->data = $_POST;
            }
        }
    }

    /**
     * Parse request headers
     */
    private function parseHeaders()
    {
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('HTTP_', '', $key);
                $header = str_replace('_', '-', $header);
                $header = strtolower($header);
                $this->headers[$header] = $value;
            }
        }
    }

    /**
     * Get all request data
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Get a specific input value
     */
    public function input($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if input exists
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Get only specific keys from input
     */
    public function only($keys)
    {
        $result = [];
        foreach ($keys as $key) {
            if (isset($this->data[$key])) {
                $result[$key] = $this->data[$key];
            }
        }
        return $result;
    }

    /**
     * Get all except specific keys
     */
    public function except($keys)
    {
        $result = $this->data;
        foreach ($keys as $key) {
            unset($result[$key]);
        }
        return $result;
    }

    /**
     * Get a header value
     */
    public function header($key, $default = null)
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get all headers
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Get the request method
     */
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get the request URI
     */
    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Get the request path
     */
    public function path()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax()
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    /**
     * Get client IP address
     */
    public function ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? null;
        }
    }

    /**
     * Validate request data
     */
    public function validate($rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $ruleArray = is_array($rule) ? $rule : explode('|', $rule);
            
            foreach ($ruleArray as $r) {
                if ($r === 'required' && !$this->has($field)) {
                    $errors[$field][] = "The $field field is required";
                }
                
                if (strpos($r, 'min:') === 0 && $this->has($field)) {
                    $min = (int) substr($r, 4);
                    if (strlen($this->input($field)) < $min) {
                        $errors[$field][] = "The $field must be at least $min characters";
                    }
                }
                
                if (strpos($r, 'max:') === 0 && $this->has($field)) {
                    $max = (int) substr($r, 4);
                    if (strlen($this->input($field)) > $max) {
                        $errors[$field][] = "The $field must not exceed $max characters";
                    }
                }
                
                if ($r === 'numeric' && $this->has($field)) {
                    if (!is_numeric($this->input($field))) {
                        $errors[$field][] = "The $field must be numeric";
                    }
                }
                
                if ($r === 'email' && $this->has($field)) {
                    if (!filter_var($this->input($field), FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "The $field must be a valid email address";
                    }
                }
            }
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        return true;
    }
}
