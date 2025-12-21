<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'MVC System') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Кастомные стили -->
    <style>
        /* Основные стили */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.3rem;
        }

        .navbar-brand i {
            margin-right: 8px;
        }

        /* Стили для главной страницы */
        .jumbotron {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .jumbotron h1 {
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .jumbotron .lead {
            font-size: 1.25rem;
            opacity: 0.9;
        }

        /* Стили для карточек возможностей */
        .feature-card {
            border: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        /* Подвал */
        .footer {
            background-color: #f5f5f5;
            padding: 2rem 0;
            margin-top: 3rem;
            border-top: 1px solid #e9ecef;
        }

        .footer h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .footer ul {
            padding-left: 0;
            list-style: none;
        }

        .footer ul li {
            margin-bottom: 0.5rem;
        }

        .footer ul li a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer ul li a:hover {
            color: #007bff;
        }

        /* Утилиты */
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Стили для уведомлений */
        .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Стили для навигации */
        .navbar {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 0.8rem 0;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 5px;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body>
<!-- Навигация -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">
            <i class="bi bi-gear-fill"></i>
            <span class="text-gradient">MVC System</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '/') === '/' ? 'active' : '' ?>" href="/">
                        <i class="bi bi-house"></i> Главная
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/about') !== false ? 'active' : '' ?>" href="/about">
                        <i class="bi bi-info-circle"></i> О системе
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/docs') !== false ? 'active' : '' ?>" href="/docs">
                        <i class="bi bi-file-text"></i> Документация
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="/admin">
                            <i class="bi bi-speedometer2"></i> Панель управления
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light" href="/logout">
                            <i class="bi bi-box-arrow-right"></i> Выйти
                            <small class="ms-1">(<?= htmlspecialchars($_SESSION['username'] ?? 'Администратор') ?>)</small>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-light btn-sm ms-2" href="/login">
                            <i class="bi bi-box-arrow-in-right"></i> Войти
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-warning btn-sm ms-2" href="/quick-login">
                            <i class="bi bi-lightning"></i> Быстрый вход
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Основной контент -->
<main class="container my-4">
    <!-- Флеш-сообщения -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Контент страницы -->
    <?php if (isset($content)): ?>
        <?= $content ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Контент не загружен
        </div>
    <?php endif; ?>
</main>

<!-- Подвал -->
<footer class="footer mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5 class="mb-3">
                    <i class="bi bi-gear text-primary"></i> MVC System
                </h5>
                <p class="text-muted">
                    Модульная система управления контентом с поддержкой плагинов и виджетов.
                    Построена на современном стеке технологий.
                </p>
            </div>

            <div class="col-lg-4 mb-4">
                <h5 class="mb-3">Быстрые ссылки</h5>
                <ul>
                    <li><a href="/"><i class="bi bi-chevron-right"></i> Главная</a></li>
                    <li><a href="/about"><i class="bi bi-chevron-right"></i> О системе</a></li>
                    <li><a href="/docs"><i class="bi bi-chevron-right"></i> Документация</a></li>
                    <li><a href="/admin"><i class="bi bi-chevron-right"></i> Панель управления</a></li>
                    <li><a href="/login"><i class="bi bi-chevron-right"></i> Вход в систему</a></li>
                </ul>
            </div>

            <div class="col-lg-4 mb-4">
                <h5 class="mb-3">Технологии</h5>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-primary">PHP 8.3</span>
                    <span class="badge bg-success">MVC</span>
                    <span class="badge bg-info">Bootstrap 5</span>
                    <span class="badge bg-warning">Плагины</span>
                    <span class="badge bg-danger">Виджеты</span>
                </div>
                <p class="text-muted mt-3 small">
                    &copy; <?= date('Y') ?> MVC System. Все права защищены.
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>

<!-- Кастомный JavaScript -->
<script>
    // Автоматическое скрытие алертов через 5 секунд
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            });
        }, 5000);

        // Анимация для карточек при загрузке
        const cards = document.querySelectorAll('.feature-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';

            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
</body>
</html>