<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\View\TemplateEngine;
use App\Services\AuthService;
use App\Core\Application;

abstract class Controller
{
    protected TemplateEngine $template;
    protected ?AuthService $authService = null;

    public function __construct(
        TemplateEngine $template = null,
        AuthService $authService = null
    ) {
        $this->template = $template ?? new TemplateEngine();
        $this->authService = $authService ?? $this->getAuthServiceFromContainer();
    }

    private function getAuthServiceFromContainer(): ?AuthService
    {
        try {
            $app = Application::getInstance();
            if ($app && method_exists($app, 'getContainer')) {
                $container = $app->getContainer();
                if ($container && $container->has(AuthService::class)) {
                    return $container->get(AuthService::class);
                }
            }
        } catch (\Exception $e) {
            // Только в режиме отладки логируем ошибку
            if (env('APP_DEBUG', false)) {
                error_log("Controller: Error getting AuthService from container: " . $e->getMessage());
            }
        }

        return null;
    }

    protected function view($template, $data = [])
    {
        return $this->template->render($template, $data);
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect($url, $statusCode = 302)
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    protected function isLoggedIn(): bool
    {
        return $this->authService && $this->authService->isLoggedIn();
    }

    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            $this->redirect('/login');
            return false;
        }
        return true;
    }

    protected function validateCsrfToken(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        // Если есть authService, используем его метод
        if ($this->authService) {
            if (!$this->authService->validateCsrfToken($token)) {
                $this->handleCsrfError();
                return false;
            }
            return true;
        }

        // Иначе проверяем напрямую через сессию
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->handleCsrfError();
            return false;
        }

        return true;
    }

    private function handleCsrfError(): void
    {
        if ($this->isJsonRequest()) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        } else {
            $_SESSION['flash_error'] = 'Недействительный CSRF токен';
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }

    protected function isJsonRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        return strpos($accept, 'application/json') !== false ||
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    }

    protected function requireApiAuth()
    {
        if (!$this->isLoggedIn()) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        return true;
    }

    protected function getCurrentUser()
    {
        return $this->authService ? $this->authService->getCurrentUser() : null;
    }
}