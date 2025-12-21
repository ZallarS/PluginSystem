<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
    private const SESSION_USER_KEY = 'user_id';
    private const SESSION_USERNAME_KEY = 'username';
    private const SESSION_IS_ADMIN_KEY = 'is_admin';

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function attemptLogin(string $username, string $password): bool
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user && $user->verifyPassword($password)) {
            $this->login($user);
            return true;
        }

        return false;
    }

    public function login(User $user): void
    {
        $_SESSION[self::SESSION_USER_KEY] = $user->getId();
        $_SESSION[self::SESSION_USERNAME_KEY] = $user->getUsername();
        $_SESSION[self::SESSION_IS_ADMIN_KEY] = $user->isAdmin();

        // Регенерируем сессию для предотвращения фиксации
        session_regenerate_id(true);

        // Обновляем CSRF токен
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Сбрасываем счетчик запросов для регенерации сессии
        $_SESSION['request_count'] = 0;
    }

    public function logout(): void
    {
        // Очищаем все данные сессии
        $_SESSION = [];

        // Удаляем cookie сессии
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return isset($_SESSION[self::SESSION_USER_KEY]) &&
            isset($_SESSION[self::SESSION_IS_ADMIN_KEY]) &&
            $_SESSION[self::SESSION_IS_ADMIN_KEY];
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $_SESSION[self::SESSION_USER_KEY] ?? null;
        if ($userId) {
            return $this->userRepository->find($userId);
        }

        return null;
    }

    public function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], $token);
    }

    public function getCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function checkPasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Пароль должен содержать минимум 8 символов';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Пароль должен содержать хотя бы одну заглавную букву';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Пароль должен содержать хотя бы одну строчную букву';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Пароль должен содержать хотя бы одну цифру';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Пароль должен содержать хотя бы один специальный символ';
        }

        return $errors;
    }
}