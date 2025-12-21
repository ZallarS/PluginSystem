<?php

namespace Core\Session;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();

            // Инициализируем user_widgets если не существует
            if (!isset($_SESSION['user_widgets'])) {
                $_SESSION['user_widgets'] = [];
            }
        }
    }

    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function clear()
    {
        session_unset();
    }

    public function destroy()
    {
        session_destroy();
    }

    public function regenerate($deleteOldSession = false)
    {
        session_regenerate_id($deleteOldSession);
    }

    public function all()
    {
        return $_SESSION;
    }
}