<?php
declare(strict_types=1);

namespace App\Http;

class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private array $cookies;
    private array $headers;

    public function __construct(
        array $get = null,
        array $post = null,
        array $files = null,
        array $server = null,
        array $cookies = null,
        array $headers = null
    ) {
        $this->get = $get ?? $_GET;
        $this->post = $post ?? $_POST;
        $this->files = $files ?? $_FILES;
        $this->server = $server ?? $_SERVER;
        $this->cookies = $cookies ?? $_COOKIE;
        $this->headers = $headers ?? $this->extractHeaders($server ?? $_SERVER);
    }

    public static function createFromGlobals(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER, $_COOKIE);
    }

    private function extractHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, $default = null)
    {
        return $this->post($key, $this->get($key, $default));
    }

    public function server(string $key, $default = null)
    {
        return $this->server[$key] ?? $default;
    }

    public function header(string $key, $default = null)
    {
        return $this->headers[$key] ?? $default;
    }

    public function method(): string
    {
        return $this->server('REQUEST_METHOD', 'GET');
    }

    public function uri(): string
    {
        return parse_url($this->server('REQUEST_URI', '/'), PHP_URL_PATH);
    }

    public function isJson(): bool
    {
        return strpos($this->header('Accept', ''), 'application/json') !== false ||
            $this->header('Content-Type') === 'application/json';
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function getCsrfToken(): ?string
    {
        return $this->post('_token') ?: $this->header('X-CSRF-TOKEN');
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }
}