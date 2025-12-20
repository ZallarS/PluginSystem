<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Панель администратора' ?> | MVC System</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <!-- Custom Admin CSS -->
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }

        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
            padding: 0.5rem 1rem;
        }

        .sidebar .nav-link.active {
            color: #0d6efd;
        }

        .sidebar .nav-link:hover {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
        }

        .sidebar-heading {
            font-size: .75rem;
            text-transform: uppercase;
            padding: 0.5rem 1rem;
            color: #6c757d;
        }

        main {
            padding-top: 1.5rem;
        }

        .plugin-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }

        .plugin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .plugin-status {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 10px;
        }

        .plugin-status.active {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .plugin-status.inactive {
            background-color: #f8d7da;
            color: #842029;
        }

        .badge-version {
            font-size: 0.7rem;
            background-color: #6c757d;
        }
    </style>
</head>
<body>
<!-- Навбар -->
<nav class="navbar navbar-dark bg-dark fixed-top navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="/admin">
            <i class="bi bi-gear"></i> MVC Admin
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0 ? 'active' : '' ?>" href="/admin">
                        <i class="bi bi-speedometer2"></i> Панель управления
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/plugins') !== false ? 'active' : '' ?>" href="/admin/plugins">
                        <i class="bi bi-plug"></i> Плагины
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= $_SESSION['username'] ?? 'Администратор' ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/"><i class="bi bi-house"></i> На сайт</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right"></i> Выйти</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Основной контент -->
<div class="container-fluid" style="margin-top: 70px;">
    <div class="row">
        <!-- Боковая панель -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="sidebar-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Управление</span>
                </h6>

                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/admin' ? 'active' : '' ?>" href="/admin">
                            <i class="bi bi-speedometer2"></i> Дашборд
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/plugins') !== false ? 'active' : '' ?>" href="/admin/plugins">
                            <i class="bi bi-plug"></i> Плагины
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-palette"></i> Темы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-shield-check"></i> Безопасность
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-gear"></i> Настройки
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Сайт</span>
                </h6>

                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <i class="bi bi-eye"></i> Просмотр сайта
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-file-earmark-plus"></i> Страницы
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-newspaper"></i> Статьи
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-people"></i> Пользователи
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Основной контент -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Флеш-сообщения -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <?= $_SESSION['flash_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <?= $_SESSION['flash_error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Контент страницы -->
            <?php
            // Включаем контент страницы
            if (isset($content)) {
                echo $content;
            } else {
                // Для обратной совместимости - используем текущий механизм рендеринга
            }
            ?>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom Admin JS -->
<script>
    // Автоматическое скрытие алертов через 5 секунд
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Подтверждение действий
    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }
</script>
</body>
</html>