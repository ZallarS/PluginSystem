<?php
declare(strict_types=1);

namespace App\Core\View;

class TemplateEngine
{
    private string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?: dirname(__DIR__, 3) . '/resources/views';
    }

    public function render($template, $data = []): string
    {
        // Извлекаем переменные
        extract($data);

        // Начинаем буферизацию
        ob_start();

        // Загружаем шаблон
        $this->loadTemplate($template);

        // Получаем содержимое
        $content = ob_get_clean();

        return $content;
    }

    private function loadTemplate($template): void
    {
        $templatePath = $this->resolveTemplatePath($template);

        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template} (looked in: {$templatePath})");
        }

        // Включаем файл шаблона
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