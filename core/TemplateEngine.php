<?php

namespace Core;

class TemplateEngine
{
    public function render($template, $data = [])
    {
        // Извлекаем переменные из массива данных
        extract($data);

        // Начинаем буферизацию вывода
        ob_start();

        // Загружаем шаблон
        $this->loadTemplate($template);

        // Получаем содержимое буфера
        $content = ob_get_clean();

        return $content;
    }

    private function loadTemplate($template)
    {
        $templatePath = $this->resolveTemplatePath($template);

        if (!file_exists($templatePath)) {
            throw new \Exception("Template not found: {$template}");
        }

        // Включаем файл шаблона
        include $templatePath;
    }

    private function resolveTemplatePath($template)
    {
        $template = str_replace('.', '/', $template);
        $basePath = __DIR__ . '/../app/Views/';

        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }

        return $basePath . $template;
    }

    public function include($template, $data = [])
    {
        extract($data);
        include $this->resolveTemplatePath($template);
    }
}