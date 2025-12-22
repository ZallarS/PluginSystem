<?php
declare(strict_types=1);

namespace App\Http;

class Response
{
    private $content;
    private int $statusCode;
    private array $headers;

    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_merge([
            'Content-Type' => 'text/html; charset=utf-8'
        ], $headers);
    }

    public static function json($data, int $statusCode = 200): self
    {
        return new self(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $statusCode,
            ['Content-Type' => 'application/json']
        );
    }

    public static function redirect(string $url, int $statusCode = 302): self
    {
        return new self('', $statusCode, ['Location' => $url]);
    }

    public static function view(string $template, array $data = [], int $statusCode = 200): self
    {
        // Здесь будет логика рендеринга шаблона
        $content = "Render view: {$template}";
        return new self($content, $statusCode);
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        echo $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
}