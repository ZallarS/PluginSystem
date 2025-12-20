<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'MVC System' ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <style>
        .navbar-brand {
            font-weight: bold;
        }

        .footer {
            background-color: #f5f5f5;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        .feature-icon {
            font-size: 2rem;
            color: #0d6efd;
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
                    <a class="nav-link" href="/">Главная</a>
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
                            <i class="bi bi-box-arrow-right"></i> Выйти (<?= $_SESSION['username'] ?>)
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
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <?php $this->template->yield('content'); ?>
</main>

<!-- Подвал -->
<footer class="footer mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>MVC System</h5>
                <p>Модульная система управления контентом с поддержкой плагинов.</p>
            </div>
            <div class="col-md-4">
                <h5>Разделы</h5>
                <ul class="list-unstyled">
                    <li><a href="/" class="text-decoration-none">Главная</a></li>
                    <li><a href="/about" class="text-decoration-none">О системе</a></li>
                    <li><a href="/docs" class="text-decoration-none">Документация</a></li>
                    <li><a href="/admin" class="text-decoration-none">Панель управления</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Технологии</h5>
                <p>PHP 8.0+, MVC архитектура, Bootstrap 5, система плагинов.</p>
                <p class="text-muted small">&copy; <?= date('Y') ?> MVC System</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Автоматическое скрытие алертов
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
</script>
</body>
</html>