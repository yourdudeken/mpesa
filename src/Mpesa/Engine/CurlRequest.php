<?php
namespace Yourdudeken\Mpesa\Engine;

use Yourdudeken\Mpesa\Contracts\HttpRequest;

class CurlRequest implements HttpRequest
{
    /** @var \CurlHandle|resource|null */
    protected $handle = null;

    public function __construct()
    {
        $this->handle = curl_init();
    }

    /**
     * Perform an HTTP request.
     * 
     * @param string $method
     * @param string $url
     * @param array $options
     * @return object
     */
    public function request(string $method, string $url, array $options = []): object
    {
        $this->reset();
        
        $method = strtoupper($method);
        $headers = $options['headers'] ?? [];
        $body = $options['json'] ?? ($options['body'] ?? null);

        if ($method === 'GET' && !empty($options['query'])) {
            $url .= '?' . http_build_query($options['query']);
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);

        if ($body) {
            if (is_array($body)) {
                $body = json_encode($body);
                $headers[] = 'Content-Type: application/json';
            }
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, $body);
        }

        if (!empty($options['auth'])) {
            curl_setopt($this->handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->handle, CURLOPT_USERPWD, implode(':', $options['auth']));
        }

        if (!empty($headers)) {
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);
        }

        // Add some default timeouts
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($this->handle);
        $statusCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        $error = curl_error($this->handle);

        return (object) [
            'getStatusCode' => fn() => $statusCode,
            'getBody' => fn() => $response,
            'getError' => fn() => $error
        ];
    }

    public function setOption(int $name, mixed $value): self
    {
        curl_setopt($this->handle, $name, $value);
        return $this;
    }

    public function getInfo(?int $name = null): mixed
    {
        return curl_getinfo($this->handle, $name);
    }

    public function error(): string
    {
        return curl_error($this->handle);
    }

    public function reset(): void
    {
        curl_reset($this->handle);
    }

    public function execute(): mixed
    {
        return curl_exec($this->handle);
    }

    public function close(): void
    {
        if ($this->handle) {
            curl_close($this->handle);
        }
    }
}