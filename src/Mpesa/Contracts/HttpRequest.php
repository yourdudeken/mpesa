<?php

namespace Yourdudeken\Mpesa\Contracts;
/**
 * Interface HttpRequest
 *
 * @category PHP
 *
 * @author   Kennedy Muthengi <kenmwendwamuthengi@gmail.com>
 */

interface HttpRequest
{
    /**
     * Perform an HTTP request.
     * 
     * @param string $method
     * @param string $url
     * @param array $options
     * @return object
     */
    public function request(string $method, string $url, array $options = []): object;

    /**
     * Set a low-level option (e.g., CURLOPT_*)
     */
    public function setOption(int $name, mixed $value): self;

    /**
     * Get information about the last request.
     */
    public function getInfo(?int $name = null): mixed;

    /**
     * Get the last error message.
     */
    public function error(): string;

    /**
     * Reset the connection.
     */
    public function reset(): void;

    /**
     * Execute the prepared request.
     */
    public function execute(): mixed;

    /**
     * Close the connection.
     */
    public function close(): void;
}
