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
        $this->authService = $authService;

        // Если AuthService не передан, пытаемся получить из контейнера
        if (!$this->authService) {
            $this->authService = $this->getAuthServiceFromContainer();
        }
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
            // Молча игнорируем, вернем null
        }

        return null;
    }

    private function initializeAuthService(): void
    {
        // Сначала пытаемся получить из контейнера через Application
        try {
            $app = \App\Core\Application::getInstance();
            if ($app && method_exists($app, 'getContainer')) {
                $container = $app->getContainer();
                if ($container && $container->has(AuthService::class)) {
                    $this->authService = $container->get(AuthService::class);
                    error_log("Controller: AuthService loaded from container");
                    return;
                }
            }
        } catch (Exception $e) {
            error_log("Controller: Error getting AuthService from container: " . $e->getMessage());
        }

        // Fallback: создаем AuthService напрямую
        try {
            $this->authService = $this->createDirectAuthService();
            error_log("Controller: AuthService created directly");
        } catch (Exception $e) {
            error_log("Controller: Failed to create AuthService: " . $e->getMessage());
            $this->authService = $this->createMinimalAuthService();
        }
    }

    /**
     * Создает AuthService напрямую, минуя контейнер
     */
    private function createDirectAuthService(): AuthService
    {
        // Пробуем создать PDO соединение
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            env('DB_HOST', 'localhost'),
            env('DB_PORT', '3306'),
            env('DB_DATABASE', 'SystemPlugins')
        );

        try {
            $pdo = new \PDO(
                $dsn,
                env('DB_USERNAME', 'root'),
                env('DB_PASSWORD', ''),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            $userRepository = new \App\Repositories\UserRepository($pdo);
            error_log("Controller: Database connection successful");
            return new AuthService($userRepository);

        } catch (\PDOException $e) {
            error_log("Controller: PDO error, using fallback repository: " . $e->getMessage());

            // Используем fallback репозиторий без БД
            $fallbackRepository = new class {
                public function findByUsername($username) {
                    $adminUsername = env('ADMIN_USERNAME', 'admin');
                    $adminPassword = env('ADMIN_PASSWORD', 'admin');

                    if ($username === $adminUsername) {
                        $user = new \App\Models\User($adminUsername, $adminPassword, true);
                        $userData = $user->toArray();
                        $userData['id'] = 1;
                        return \App\Models\User::createFromArray($userData);
                    }
                    return null;
                }

                public function find($id) {
                    if ($id === 1) {
                        $adminUsername = env('ADMIN_USERNAME', 'admin');
                        $adminPassword = env('ADMIN_PASSWORD', 'admin');
                        $user = new \App\Models\User($adminUsername, $adminPassword, true);
                        $userData = $user->toArray();
                        $userData['id'] = 1;
                        return \App\Models\User::createFromArray($userData);
                    }
                    return null;
                }

                public function save($user) { return true; }
                public function createTable() { }
            };

            return new AuthService($fallbackRepository);
        }
    }

    /**
     * Создает минимальный AuthService для работы без БД
     */
    private function createMinimalAuthService(): AuthService
    {
        error_log("Controller: Creating minimal AuthService");

        return new AuthService(new class {
            public function findByUsername($username) {
                $adminUsername = env('ADMIN_USERNAME', 'admin');
                $adminPassword = env('ADMIN_PASSWORD', 'admin');

                if ($username === $adminUsername) {
                    $user = new \App\Models\User($adminUsername, $adminPassword, true);
                    $userData = $user->toArray();
                    $userData['id'] = 1;
                    return \App\Models\User::createFromArray($userData);
                }
                return null;
            }

            public function find($id) {
                if ($id === 1) {
                    $adminUsername = env('ADMIN_USERNAME', 'admin');
                    $adminPassword = env('ADMIN_PASSWORD', 'admin');
                    $user = new \App\Models\User($adminUsername, $adminPassword, true);
                    $userData = $user->toArray();
                    $userData['id'] = 1;
                    return \App\Models\User::createFromArray($userData);
                }
                return null;
            }

            public function save($user) { return true; }
            public function createTable() { }
        });
    }

    // ... остальные методы БЕЗ ИЗМЕНЕНИЙ
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

        if (!$this->authService) {
            return false;
        }

        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!$this->authService->validateCsrfToken($token)) {
            if ($this->isJsonRequest()) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
                return false;
            } else {
                $_SESSION['flash_error'] = 'Недействительный CSRF токен';
                $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
                return false;
            }
        }

        return true;
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