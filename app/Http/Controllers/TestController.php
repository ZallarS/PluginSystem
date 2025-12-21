<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class TestController extends Controller
{
    public function index()
    {
        echo "<h1>✅ System Test - ВСЕ РАБОТАЕТ!</h1>";
        echo "<p>Контроллер успешно загружен!</p>";

        echo "<h3>Состояние системы:</h3>";
        echo "<ul>";
        echo "<li>AuthService: " . ($this->authService ? "✅ Доступен" : "❌ Не доступен") . "</li>";
        echo "<li>Template Engine: " . ($this->template ? "✅ Доступен" : "❌ Не доступен") . "</li>";
        echo "<li>Вход выполнен: " . ($this->isLoggedIn() ? "✅ Да" : "❌ Нет") . "</li>";
        echo "<li>Сессия: " . session_status() . " (2 = PHP_SESSION_ACTIVE)</li>";
        echo "</ul>";

        echo "<h3>Действия:</h3>";
        echo '<p><a href="/">🏠 На главную</a></p>';
        echo '<p><a href="/admin">⚙️ В админку</a></p>';
        echo '<p><a href="/login">🔑 Войти</a></p>';
    }
}