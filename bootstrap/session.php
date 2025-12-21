<?php
declare(strict_types=1);

// Инициализация сессии с безопасными настройками
if (session_status() === PHP_SESSION_NONE) {
    // Безопасные настройки сессии
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');

    session_start();

    // Регенерируем ID сессии каждые 10 запросов для предотвращения фиксации сессии
    if (!isset($_SESSION['request_count'])) {
        $_SESSION['request_count'] = 0;
    }

    $_SESSION['request_count']++;

    if ($_SESSION['request_count'] > 10) {
        session_regenerate_id(true);
        $_SESSION['request_count'] = 0;
    }

    // Инициализируем CSRF токен если его нет
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Инициализируем user_widgets если не существует
    if (!isset($_SESSION['user_widgets'])) {
        $_SESSION['user_widgets'] = [];
    }
}