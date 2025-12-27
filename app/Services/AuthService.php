<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Core\Session\SessionInterface;

/**
 * AuthService class
 *
 * Handles user authentication including login, logout,
 * session management, and CSRF token generation.
 *
 * @package App\Services
 */
class AuthService
{
    /**
     * @var string Session key for user ID
     */
    private const SESSION_USER_KEY = 'auth.user_id';

    /**
     * @var string Session key for username
     */
    private const SESSION_USERNAME_KEY = 'auth.username';

    /**
     * @var string Session key for admin status
     */
    private const SESSION_IS_ADMIN_KEY = 'auth.is_admin';

    /**
     * @var string Session key for CSRF token
     */
    private const SESSION_CSRF_KEY = 'auth.csrf_token';

    /**
     * @var UserRepository The user repository instance
     */
    private UserRepository $userRepository;

    /**
     * @var SessionInterface The session interface instance
     */
    private SessionInterface $session;


    /**
     * Create a new authentication service instance.
     *
     * @param UserRepository $userRepository The user repository
     * @param SessionInterface $session The session interface
     */
    public function __construct(UserRepository $userRepository, SessionInterface $session)
    {
        $this->userRepository = $userRepository;
        $this->session = $session;
    }

    /**
     * Attempt to log in a user with the given credentials.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool True if login was successful
     */
    public function attemptLogin(string $username, string $password): bool
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user && $user->verifyPassword($password)) {
            $this->login($user);

            return true;
        }

        return false;
    }

    /**
     * Log in a user.
     *
     * Sets session variables for the user, regenerates the session ID,
     * and generates a new CSRF token.
     *
     * @param User $user The user to log in
     * @return void
     */
    public function login(User $user): void
    {
        // Используем новые ключи сессии
        $this->session->set(self::SESSION_USER_KEY, $user->getId());
        $this->session->set(self::SESSION_USERNAME_KEY, $user->getUsername());
        $this->session->set(self::SESSION_IS_ADMIN_KEY, $user->isAdmin());

        // Регенерируем сессию для предотвращения фиксации
        $this->session->regenerate();

        // Обновляем CSRF токен
        $this->generateCsrfToken();
    }

    /**
     * Log out the current user.
     *
     * Destroys the session and clears all authentication data.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();
    }

    /**
     * Check if a user is currently logged in.
     *
     * @return bool True if user is logged in
     */
    public function isLoggedIn(): bool
    {
        // Используем новые ключи
        return $this->session->has(self::SESSION_USER_KEY) &&
            $this->session->has(self::SESSION_IS_ADMIN_KEY) &&
            $this->session->get(self::SESSION_IS_ADMIN_KEY) === true;
    }

    /**
     * Get the currently logged in user.
     *
     * @return User|null The current user or null if not logged in
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $userId = $this->session->get(self::SESSION_USER_KEY);
        return $this->userRepository->find($userId);
    }

    /**
     * Get the current CSRF token.
     *
     * Generates a new token if one doesn't exist.
     *
     * @return string The CSRF token
     */
    public function getCsrfToken(): string
    {
        $token = $this->session->get(self::SESSION_CSRF_KEY);

        if (!$token) {
            $token = $this->generateCsrfToken();
        }

        return $token;
    }

    /**
     * Validate a CSRF token.
     *
     * @param string $token The token to validate
     * @return bool True if the token is valid
     */
    public function validateCsrfToken(string $token): bool
    {
        $storedToken = $this->session->get(self::SESSION_CSRF_KEY);
        return $storedToken && hash_equals($storedToken, $token);
    }

    /**
     * Generate a new CSRF token.
     *
     * @return string The generated token
     */
    private function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set(self::SESSION_CSRF_KEY, $token);
        return $token;
    }
}
