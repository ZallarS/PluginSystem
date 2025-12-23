<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Core\Session\SessionManager;

class AuthService
{
    private const SESSION_USER_KEY = 'auth.user_id';
    private const SESSION_USERNAME_KEY = 'auth.username';
    private const SESSION_IS_ADMIN_KEY = 'auth.is_admin';
    private const SESSION_CSRF_KEY = 'auth.csrf_token';

    private UserRepository $userRepository;
    private SessionManager $session;

    public function __construct(UserRepository $userRepository, SessionManager $session)
    {
        $this->userRepository = $userRepository;
        $this->session = $session;
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
        $this->session->set(self::SESSION_USER_KEY, $user->getId());
        $this->session->set(self::SESSION_USERNAME_KEY, $user->getUsername());
        $this->session->set(self::SESSION_IS_ADMIN_KEY, $user->isAdmin());

        // Регенерируем сессию для предотвращения фиксации
        $this->session->regenerate();

        // Обновляем CSRF токен
        $this->generateCsrfToken();
    }

    public function logout(): void
    {
        $this->session->destroy();
    }

    public function isLoggedIn(): bool
    {
        return $this->session->has(self::SESSION_USER_KEY) &&
            $this->session->has(self::SESSION_IS_ADMIN_KEY) &&
            $this->session->get(self::SESSION_IS_ADMIN_KEY) === true;
    }

    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $this->session->get(self::SESSION_USER_KEY);
        return $this->userRepository->find($userId);
    }

    public function getCsrfToken(): string
    {
        $token = $this->session->get(self::SESSION_CSRF_KEY);

        if (!$token) {
            $token = $this->generateCsrfToken();
        }

        return $token;
    }

    public function validateCsrfToken(string $token): bool
    {
        $storedToken = $this->session->get(self::SESSION_CSRF_KEY);
        return $storedToken && hash_equals($storedToken, $token);
    }

    private function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set(self::SESSION_CSRF_KEY, $token);
        return $token;
    }

}