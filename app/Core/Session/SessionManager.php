<?php
declare(strict_types=1);

namespace App\Core\Session;

class SessionManager
{
    private bool $started = false;
    private array $flashMessages = [];

    public function start(array $options = []): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Безопасные настройки
        $defaultOptions = [
            'cookie_httponly' => '1',
            'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0',
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => '1',
            'use_only_cookies' => '1',
        ];

        foreach (array_merge($defaultOptions, $options) as $key => $value) {
            ini_set("session.$key", (string)$value);
        }

        session_start();
        $this->started = true;

        // Инициализируем flash сообщения
        $this->flashMessages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
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
        return $this->flashMessages[$key] ?? $default;
    }

    public function regenerate(): void
    {
        $this->ensureStarted();
        session_regenerate_id(true);
    }

    public function destroy(): void
    {
        if ($this->started) {
            session_destroy();
            $this->started = false;
        }
    }

    private function ensureStarted(): void
    {
        if (!$this->started) {
            $this->start();
        }
    }

    public function isStarted(): bool
    {
        return $this->started;
    }
}