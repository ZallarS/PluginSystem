<?php
declare(strict_types=1);

namespace App\Core\Session;

class SessionManager implements SessionInterface
{
    private bool $started = false;
    private array $flashMessages = [];

    public function start(array $options = []): void
    {

        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            // Если сессия уже запущена, синхронизируем состояние
            if (session_status() === PHP_SESSION_ACTIVE && !$this->started) {
                $this->started = true;
                $this->flashMessages = $_SESSION['_flash'] ?? [];
                unset($_SESSION['_flash']);
            }
            return;
        }

        // Безопасные настройки
        $defaultOptions = [
            'cookie_httponly' => '1',
            'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0',
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => '1',
            'use_only_cookies' => '1',
            'gc_maxlifetime' => 1440,
            'cookie_lifetime' => 0,
        ];

        foreach (array_merge($defaultOptions, $options) as $key => $value) {
            ini_set("session.$key", (string)$value);
        }

        session_start();
        $this->started = true;

        // Инициализируем flash сообщения
        $this->flashMessages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        $this->removeLegacyKeys();

    }

    /**
     * Удаляет legacy ключи сессии
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

    public function get(string $key, $default = null)
    {
        $this->ensureStarted();

        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->ensureStarted();

        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    public function flash(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION['_flash'][$key] = $value;
    }

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

    public function regenerate(): void
    {
        $this->ensureStarted();
        session_regenerate_id(true);
    }

    public function destroy(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            // Удаляем все данные сессии
            $_SESSION = [];
            $this->flashMessages = [];

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
            $this->started = false;
        }
    }

    public function all(): array
    {
        $this->ensureStarted();
        return $_SESSION;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

    public function clear(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
        $this->flashMessages = [];
    }

    private function ensureStarted(): void
    {
        if (!$this->started) {
            $this->start();
        }
    }
}