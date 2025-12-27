<?php
declare(strict_types=1);

namespace App\Core\Session;

/**
 * SessionManager class
 *
 * A secure session management implementation that provides
 * an object-oriented interface to PHP's native session functions.
 * Handles flash messages and removes legacy session keys.
 *
 * @package App\Core\Session
 */

class SessionManager implements SessionInterface
{
    /**
     * @var bool Whether the session has been started
     */
    private bool $started = false;

    /**
     * @var array The flash messages storage
     */
    private array $flashMessages = [];


    /**
     * Start the session with the given options.
     *
     * Configures secure session settings and initializes the session.
     * If the session is already active, synchronizes the internal state.
     *
     * @param array $options Session configuration options
     * @return void
     */
    public function start(array $options = []): void
    {
        // If session is already active, just synchronize state
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            if (session_status() === PHP_SESSION_ACTIVE && !$this->started) {
                $this->started = true;
                $this->flashMessages = $_SESSION['_flash'] ?? [];
                unset($_SESSION['_flash']);
            }
            return;
        }

        // Secure session configuration
        $defaultOptions = [
            'cookie_httponly' => '1',
            'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0',
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => '1',
            'use_only_cookies' => '1',
            'gc_maxlifetime' => 1440, // 24 minutes
            'cookie_lifetime' => 0,
        ];

        // Apply merged options
        foreach (array_merge($defaultOptions, $options) as $key => $value) {
            ini_set("session.$key", (string)$value);
        }

        session_start();
        $this->started = true;

        // Initialize flash messages
        $this->flashMessages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        $this->removeLegacyKeys();
    }

    /**
     * Remove legacy session keys that are no longer used.
     *
     * Cleans up old session keys from previous versions to
     * prevent conflicts and maintain clean session data.
     *
     * @return void
     */
    private function removeLegacyKeys(): void
    {
        $legacyKeys = ['user_id', 'is_admin', 'username', 'csrf_token', 'flash_error', 'flash_message'];
        foreach ($legacyKeys as $key) {
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
        }
    }

    /**
     * Get a session value by key.
     *
     * @param string $key The key to retrieve
     * @param mixed $default The default value if key doesn't exist
     * @return mixed The session value or default
     */
    public function get(string $key, $default = null)
    {
        $this->ensureStarted();

        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value.
     *
     * @param string $key The key to set
     * @param mixed $value The value to store
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a session key exists.
     *
     * @param string $key The key to check
     * @return bool True if the key exists
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();

        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key.
     *
     * @param string $key The key to remove
     * @return void
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Set a flash message that will be available for the next request.
     *
     * Flash messages are automatically cleared after being read.
     *
     * @param string $key The flash message key
     * @param mixed $value The flash message value
     * @return void
     */
    public function flash(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get a flash message by key.
     *
     * Checks both in-memory storage and session storage to ensure
     * flash messages are available even if the session was restarted.
     *
     * @param string $key The flash message key
     * @param mixed $default The default value if key doesn't exist
     * @return mixed The flash message value or default
     */
    public function getFlash(string $key, $default = null)
    {
        // Проверяем сначала в памяти
        if (isset($this->flashMessages[$key])) {
            return $this->flashMessages[$key];
        }

        // Затем в сессии (на случай если сессия была перезапущена)
        $this->ensureStarted();
        return $_SESSION['_flash'][$key] ?? $default;
    }

    /**
     * Regenerate the session ID.
     *
     * Useful for preventing session fixation attacks.
     *
     * @return void
     */
    public function regenerate(): void
    {
        $this->ensureStarted();
        session_regenerate_id(true);
    }

    /**
     * Destroy the current session.
     *
     * Clears all session data, removes the session cookie,
     * and destroys the session.
     *
     * @return void
     */
    public function destroy(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            // Clear all session data
            $_SESSION = [];
            $this->flashMessages = [];

            // Remove session cookie
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
            $this->started = false;
        }
    }

    /**
     * Get all session data.
     *
     * @return array All session data
     */
    public function all(): array
    {
        $this->ensureStarted();
        return $_SESSION;
    }

    /**
     * Check if the session has been started.
     *
     * @return bool True if session is started
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Clear all session data without destroying the session.
     *
     * Keeps the session active but removes all data.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
        $this->flashMessages = [];
    }

    /**
     * Ensure the session is started.
     *
     * Starts the session if it hasn't been started yet.
     *
     * @return void
     */
    private function ensureStarted(): void
    {
        if (!$this->started) {
            $this->start();
        }
    }
}
