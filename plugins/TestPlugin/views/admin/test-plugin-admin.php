<?php
// Устанавливаем значения по умолчанию для переменных
$title = $title ?? 'Управление тестовым плагином';
$config = $config ?? [];
$settings = $settings ?? [];
$system_info = $system_info ?? [];
$hooks_registered = $hooks_registered ?? [];
$routes_registered = $routes_registered ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string)$title) ?> | MVC Admin</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

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

        .config-card {
            border-left: 4px solid #0d6efd;
        }

        .settings-card {
            border-left: 4px solid #198754;
        }

        .info-card {
            border-left: 4px solid #6f42c1;
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
                    <a class="nav-link" href="/admin">
                        <i class="bi bi-speedometer2"></i> Панель управления
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/plugins">
                        <i class="bi bi-plug"></i> Плагины
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/test-plugin">
                        <i class="bi bi-plugin"></i> Тестовый плагин
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars((string)($_SESSION['username'] ?? 'Администратор')) ?>
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
                    <span>Управление плагином</span>
                </h6>

                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/test-plugin">
                            <i class="bi bi-dashboard"></i> Обзор
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-gear"></i> Настройки
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-bar-chart"></i> Статистика
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-tools"></i> Инструменты
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/test-plugin" target="_blank">
                            <i class="bi bi-eye"></i> Просмотр плагина
                        </a>
                    </li>
                </ul>

                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Действия</span>
                </h6>

                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/test-plugin?action=clear_cache">
                            <i class="bi bi-trash"></i> Очистить кэш
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/test-plugin/api" target="_blank">
                            <i class="bi bi-code-slash"></i> Тест API
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/plugins">
                            <i class="bi bi-arrow-left"></i> Все плагины
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Основной контент -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Уведомления -->
            <?php include __DIR__ . '/../../../../app/Views/components/notifications.php'; ?>

            <!-- Заголовок -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-plugin"></i> <?= htmlspecialchars((string)$title) ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-check-circle"></i> Сохранить настройки
                    </button>
                </div>
            </div>

            <!-- Основная информация -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card config-card h-100">
                        <div class="card-header">
                            <i class="bi bi-info-circle"></i> Конфигурация плагина
                        </div>
                        <div class="card-body">
                            <h5><?= htmlspecialchars((string)($config['name'] ?? 'Тестовый плагин')) ?></h5>
                            <p><strong>Версия:</strong> <?= htmlspecialchars((string)($config['version'] ?? '1.0.0')) ?></p>
                            <p><strong>Автор:</strong> <?= htmlspecialchars((string)($config['author'] ?? 'Unknown')) ?></p>
                            <p><strong>Лицензия:</strong> <?= htmlspecialchars((string)($config['license'] ?? 'MIT')) ?></p>
                            <p><strong>Описание:</strong> <?= htmlspecialchars((string)($config['description'] ?? 'Нет описания')) ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card settings-card h-100">
                        <div class="card-header">
                            <i class="bi bi-gear"></i> Текущие настройки
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Функционал включен</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                        <?= ($settings['enable_feature'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        <?= ($settings['enable_feature'] ?? false) ? 'Включено' : 'Выключено' ?>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Максимум элементов</label>
                                <input type="number" class="form-control"
                                       value="<?= htmlspecialchars((string)($settings['max_items'] ?? 10)) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Приветственное сообщение</label>
                                <input type="text" class="form-control"
                                       value="<?= htmlspecialchars((string)($settings['custom_message'] ?? 'Добро пожаловать!')) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card info-card h-100">
                        <div class="card-header">
                            <i class="bi bi-hdd"></i> Системная информация
                        </div>
                        <div class="card-body">
                            <p><strong>PHP версия:</strong> <?= htmlspecialchars((string)($system_info['php_version'] ?? PHP_VERSION)) ?></p>
                            <p><strong>Путь к плагину:</strong> <small><?= htmlspecialchars((string)($system_info['plugin_path'] ?? '')) ?></small></p>
                            <p><strong>Статус:</strong> <span class="badge bg-success">Активен</span></p>
                            <p><strong>Расширений PHP:</strong> <?= count($system_info['loaded_extensions'] ?? []) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Маршруты и хуки -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-signpost"></i> Зарегистрированные маршруты
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Метод</th>
                                        <th>URL</th>
                                        <th>Действие</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($routes_registered as $route): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?= htmlspecialchars((string)($route['method'] ?? 'GET')) ?></span></td>
                                            <td><code><?= htmlspecialchars((string)($route['uri'] ?? '')) ?></code></td>
                                            <td><small><?= htmlspecialchars((string)($route['action'] ?? '')) ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-link"></i> Зарегистрированные хуки
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($hooks_registered as $hook): ?>
                                    <li class="list-group-item">
                                        <i class="bi bi-link-45deg text-success me-2"></i>
                                        <?= htmlspecialchars((string)$hook) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Действия с плагином -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-tools"></i> Действия с плагином
                        </div>
                        <div class="card-body">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Обновить плагин
                                </button>

                                <button type="button" class="btn btn-outline-warning">
                                    <i class="bi bi-file-earmark-code"></i> Просмотр логов
                                </button>

                                <button type="button" class="btn btn-outline-info">
                                    <i class="bi bi-database"></i> Проверить БД
                                </button>

                                <a href="/admin/plugins/deactivate/TestPlugin" class="btn btn-outline-danger">
                                    <i class="bi bi-power"></i> Деактивировать
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>