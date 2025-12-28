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
        try {
            extract($data);
            ob_start();
            $this->loadTemplate($template);
            return ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            return $this->renderErrorPage($template, $e, $data);
        }
    }

    /**
     * Render an error page when template fails to load.
     *
     * @param string $template The template that failed
     * @param \Exception $e The exception
     * @param array $data The template data
     * @return string The error page HTML
     */
    private function renderErrorPage(string $template, \Exception $e, array $data = []): string
    {
        $debug = env('APP_DEBUG', false);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ошибка загрузки шаблона</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                }
                .error-container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 40px;
                    background: white;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
                }
            </style>
        </head>
        <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="error-container">
                        <div class="text-center mb-4">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
                            <h1 class="mt-3">Ошибка загрузки шаблона</h1>
                            <p class="lead">Не удалось загрузить шаблон: <code><?= htmlspecialchars($template) ?></code></p>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Это страница ошибки. Пожалуйста, сообщите об этом администратору.
                        </div>

                        <?php if ($debug): ?>
                            <div class="mt-4">
                                <h4>Отладочная информация:</h4>
                                <div class="alert alert-danger">
                                    <p class="mb-2"><strong>Ошибка:</strong> <?= htmlspecialchars($e->getMessage()) ?></p>
                                    <p class="mb-0"><strong>Файл:</strong> <?= htmlspecialchars($e->getFile()) ?> (строка <?= $e->getLine() ?>)</p>
                                </div>

                                <h5 class="mt-3">Данные шаблона:</h5>
                                <pre class="bg-light p-3 rounded" style="font-size: 12px;"><?= htmlspecialchars(print_r($data, true)) ?></pre>

                                <h5 class="mt-3">Трассировка:</h5>
                                <pre class="bg-dark text-light p-3 rounded" style="font-size: 12px; max-height: 300px; overflow: auto;"><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 text-center">
                            <a href="/" class="btn btn-primary me-2">
                                <i class="bi bi-house-door me-1"></i> На главную
                            </a>
                            <button onclick="window.location.reload()" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-clockwise me-1"></i> Обновить страницу
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
        return ob_get_clean();
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