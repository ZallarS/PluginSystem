<?php
declare(strict_types=1);

namespace App\Core\View;

class TemplateEngine
{
    private static $instance;
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?: dirname(__DIR__, 3) . '/resources/views';
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render($template, $data = []): string
    {
        extract($data);
        ob_start();
        $this->loadTemplate($template);
        return ob_get_clean();
    }

    private function loadTemplate($template): void
    {
        $templatePath = $this->resolveTemplatePath($template);

        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template} (looked in: {$templatePath})");
        }

        require $templatePath;
    }

    private function resolveTemplatePath($template): string
    {
        $template = str_replace('.', '/', $template);

        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }

        return $this->basePath . '/' . $template;
    }

    public function include($template, $data = []): void
    {
        extract($data);
        include $this->resolveTemplatePath($template);
    }
}