<?php
// Устанавливаем значения по умолчанию для переменных
$title = $title ?? 'Главная страница MVC системы';
$message = $message ?? 'Добро пожаловать в нашу MVC систему с плагинами!';
$features = $features ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string)$title) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
        .feature-icon {
            font-size: 3rem;
            color: #0d6efd;
        }

        .jumbotron {
            background-color: #f8f9fa;
            padding: 4rem 2rem;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
<!-- Навигация -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">
            <i class="bi bi-gear"></i> MVC System
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about">О системе</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/docs">Документация</a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin">
                            <i class="bi bi-speedometer2"></i> Панель управления
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="/logout">
                            <i class="bi bi-box-arrow-right"></i> Выйти (<?= htmlspecialchars((string)($_SESSION['username'] ?? '')) ?>)
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">
                            <i class="bi bi-box-arrow-in-right"></i> Войти
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Основной контент -->
<main class="container mt-4">
    <!-- Уведомления -->
    <?php include __DIR__ . '/../components/notifications.php'; ?>

    <div class="jumbotron">
        <h1 class="display-4"><?= htmlspecialchars((string)$title) ?></h1>
        <p class="lead"><?= htmlspecialchars((string)$message) ?></p>
        <hr class="my-4">

        <div class="row mt-5">
            <div class="col-md-12">
                <h3 class="mb-4">Основные возможности системы:</h3>
                <div class="row">
                    <?php foreach ($features as $index => $feature): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="feature-icon mb-3">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <h5 class="card-title"><?= htmlspecialchars((string)$feature) ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-plug"></i> Плагины</h5>
                        <p class="card-text">Система поддерживает модульную архитектуру с плагинами.</p>
                        <?php if (isset($_SESSION['is_admin'])): ?>
                            <a href="/admin/plugins" class="btn btn-primary">Управление плагинами</a>
                        <?php else: ?>
                            <a href="/login" class="btn btn-outline-primary">Войти для управления</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-palette"></i> Темы оформления</h5>
                        <p class="card-text">Гибкая система тем позволяет легко менять внешний вид.</p>
                        <a href="/themes" class="btn btn-outline-primary">Выбрать тему</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Информационный блок (не будет автоматически скрываться) -->
    <div class="alert alert-info mt-4">
        <h4><i class="bi bi-info-circle"></i> Демонстрация системы</h4>
        <p>Для тестирования административной панели используйте:</p>
        <ul>
            <li><strong>Логин:</strong> admin</li>
            <li><strong>Пароль:</strong> admin</li>
        </ul>
        <a href="/login" class="btn btn-info">Перейти к авторизации</a>
        <a href="/quick-login" class="btn btn-warning ms-2">Быстрый вход (тест)</a>
    </div>
</main>

<!-- Подвал -->
<footer class="footer mt-5 py-3 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>MVC System</h5>
                <p>Модульная система управления контентом с поддержкой плагинов.</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="text-muted">&copy; <?= date('Y') ?> MVC System. Все права защищены.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>