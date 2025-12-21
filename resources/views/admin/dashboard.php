<?php
// Устанавливаем значения по умолчанию для переменных
$title = $title ?? 'Панель администратора';
$message = $message ?? 'Добро пожаловать в панель управления!';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string)$title) ?> | MVC System</title>
    <?= csrf_meta() ?>
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

        /* Стили для виджетов */
        .dashboard-widgets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .dashboard-widget {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .dashboard-widget:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .widget-small {
            grid-column: span 1;
        }

        .widget-medium {
            grid-column: span 1;
        }

        .widget-large {
            grid-column: span 2;
        }

        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }

        .widget-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .widget-title h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .widget-title .bi {
            font-size: 18px;
            color: #0d6efd;
        }

        .widget-actions .btn {
            padding: 4px 8px;
            font-size: 12px;
        }

        .widget-body {
            padding: 20px;
        }

        /* Стили для карточек статистики */
        .stat-card {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .stat-icon {
            font-size: 24px;
            color: #0d6efd;
            margin-bottom: 10px;
        }

        .stat-info h5 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            color: #212529;
        }

        .stat-info small {
            color: #6c757d;
            font-size: 12px;
            text-transform: uppercase;
        }

        /* Стили для списка активности */
        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-list li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .activity-list li:last-child {
            border-bottom: none;
        }

        .activity-list li .bi {
            margin-right: 10px;
            font-size: 16px;
        }

        .activity-list li span {
            flex-grow: 1;
        }

        .activity-list li small {
            color: #6c757d;
            font-size: 12px;
        }

        /* Стили для быстрых ссылок */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .quick-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            text-decoration: none;
            color: #495057;
            transition: all 0.2s ease;
        }

        .quick-link:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            color: #0d6efd;
        }

        .quick-link .bi {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .quick-link span {
            font-size: 12px;
            text-align: center;
        }

        /* Стили для информации о сервере */
        .server-info .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .server-info .info-row:last-child {
            border-bottom: none;
        }

        .server-info .info-row span {
            color: #6c757d;
        }

        .server-info .info-row strong {
            color: #212529;
            font-weight: 600;
        }

        /* Стили для карточек настройки виджетов */
        .widget-config-card {
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .widget-config-card:hover {
            border-color: #0d6efd;
        }

        .widget-config-card .card-title {
            font-size: 15px;
            margin-bottom: 5px;
        }

        .widget-config-card .card-text {
            font-size: 13px;
        }
        /* Стили для панели восстановления виджетов */
        .hidden-widget-item {
            margin: 5px 0;
        }

        .hidden-widget-item .btn {
            white-space: nowrap;
        }

        /* Анимация для скрытия виджета */
        .dashboard-widget {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        /* Стили для кнопки восстановления */
        .restore-widget {
            font-size: 12px;
            padding: 2px 8px;
        }
        /* Стили для виджетов плагина */
        .weather-widget .weather-icon {
            font-size: 2rem;
        }

        .weather-widget .temperature h3 {
            font-size: 2rem;
            font-weight: bold;
        }

        .weather-forecast .col-4 {
            border-right: 1px solid #dee2e6;
        }

        .weather-forecast .col-4:last-child {
            border-right: none;
        }

        /* Стили для виджета комментариев */
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-weight: bold;
        }

        .comment-item {
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .comment-item:hover {
            background-color: #f8f9fa;
        }

        .comment-actions a {
            color: #6c757d;
            transition: color 0.3s;
        }

        .comment-actions a:hover {
            color: #0d6efd;
        }

        /* Стили для виджета системной информации */
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .info-row {
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }
        /* Стили для анимаций */
        .dashboard-widget {
            transition: opacity 0.5s ease, transform 0.5s ease, box-shadow 0.3s ease;
        }

        /* Стили для контента-заглушки */
        .placeholder-content {
            opacity: 0.8;
        }

        /* Стили для панели восстановления */
        #hiddenWidgetsPanel {
            transition: all 0.3s ease;
        }

        .hidden-widget-item {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .hidden-widget-item.removing {
            opacity: 0;
            transform: translateX(-20px);
        }

        /* Стили для уведомлений */
        .fixed-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        /* Стили для бейджей источников */
        .badge-source {
            font-size: 0.7rem;
            padding: 2px 6px;
        }

        /* Стили для карточек настроек виджетов */
        .widget-config-card {
            transition: all 0.2s ease;
            border: 1px solid #dee2e6;
        }

        .widget-config-card:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
        }

        .widget-config-card .card-title {
            font-size: 14px;
            font-weight: 600;
        }

        .widget-config-card .card-text {
            font-size: 12px;
        }

        /* Стили для кнопок восстановления */
        .restore-widget-btn {
            white-space: nowrap;
            font-size: 12px;
        }

        /* Стили для панели восстановления */
        #hiddenWidgetsPanel .card {
            border-color: #ffc107;
        }

        #hiddenWidgetsPanel .card-header {
            background-color: #fff3cd;
            color: #664d03;
        }

        /* Анимация для кнопок */
        .widget-toggle {
            transition: all 0.2s ease;
        }

        .widget-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        /* Анимации для виджетов */
        .dashboard-widget {
            transition: opacity 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-widget.adding {
            animation: widgetAppear 0.5s ease forwards;
        }

        .dashboard-widget.removing {
            animation: widgetDisappear 0.3s ease forwards;
        }

        @keyframes widgetAppear {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes widgetDisappear {
            from {
                opacity: 1;
                transform: scale(1);
            }
            to {
                opacity: 0;
                transform: scale(0.95);
            }
        }

        /* Стили для заблокированных переключателей */
        .widget-toggle-switch:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        /* Стили для несохраненных изменений */
        .widget-toggle-switch.border-primary {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25) !important;
        }

        /* Стили для кнопки сохранения при загрузке */
        .save-widgets:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Анимация для виджетов */
        @keyframes widgetAppear {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes widgetDisappear {
            from {
                opacity: 1;
                transform: scale(1);
            }
            to {
                opacity: 0;
                transform: scale(0.95);
            }
        }
        /* Анимации для виджетов */
        .dashboard-widget.appearing {
            animation: widgetAppear 0.5s ease forwards;
        }

        @keyframes widgetAppear {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes widgetDisappear {
            0% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                opacity: 0;
                transform: scale(0.95);
            }
        }

        /* Стили для кнопок восстановления */
        .restore-widget-btn {
            transition: all 0.2s ease;
        }

        .restore-widget-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .restore-widget-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        /* Стили для виджетов плагина */
        .weather-widget .weather-icon {
            font-size: 2rem;
        }

        .weather-widget .temperature h3 {
            font-size: 2rem;
            font-weight: bold;
        }

        .weather-forecast .col-4 {
            border-right: 1px solid #dee2e6;
        }

        .weather-forecast .col-4:last-child {
            border-right: none;
        }

        /* Стили для виджета комментариев */
        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .comment-item {
            transition: background-color 0.3s;
        }

        .comment-item:hover {
            background-color: #f8f9fa;
        }

        .comment-actions a {
            color: #6c757d;
            transition: color 0.3s;
            font-size: 12px;
        }

        .comment-actions a:hover {
            color: #0d6efd;
        }

        /* Стили для виджета системной информации */
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-dot.bg-success {
            background-color: #198754;
        }

        .status-dot.bg-warning {
            background-color: #ffc107;
        }

        .status-dot.bg-danger {
            background-color: #dc3545;
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
                    <span>Управление</span>
                </h6>

                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/admin' ? 'active' : '' ?>" href="/admin">
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
            </div>
        </div>

        <!-- Основной контент -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Уведомления -->
            <?php include __DIR__ . '/../components/notifications.php'; ?>

            <!-- Заголовок с кнопкой управления виджетами -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-speedometer2"></i> <?= htmlspecialchars((string)$title) ?>
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#widgetsModal">
                        <i class="bi bi-grid"></i> Управление виджетами
                    </button>
                </div>
            </div>

            <!-- Информационный блок -->
            <div class="alert alert-info">
                <h4><i class="bi bi-info-circle"></i> Добро пожаловать в панель администратора!</h4>
                <p><?= htmlspecialchars((string)$message) ?></p>
            </div>

            <!-- Сетка виджетов -->
            <?php
            $widgetManager = \App\Core\Widgets\WidgetManager::getInstance();
            echo $widgetManager->renderWidgetsGrid();
            ?>

            <!-- Панель восстановления скрытых виджетов -->
            <div id="hiddenWidgetsPanel" class="mt-4" style="display: none;">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-eye-slash"></i> Скрытые виджеты
                        <button type="button" class="btn btn-sm btn-outline-dark float-end" id="hideRestorePanel">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Виджеты, которые вы скрыли. Вы можете восстановить их:</p>
                        <div id="hiddenWidgetsList" class="d-flex flex-wrap gap-2">
                            <!-- Сюда будут добавляться скрытые виджеты -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Модальное окно управления виджетами -->
<div class="modal fade" id="widgetsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-grid"></i> Управление виджетами</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?php
                    $allWidgets = $widgetManager->getAllWidgets();
                    foreach ($allWidgets as $widget):
                        ?>
                        <div class="col-md-6 mb-3">
                            <div class="card widget-config-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title">
                                                <i class="bi <?= $widget['icon'] ?>"></i>
                                                <?= htmlspecialchars($widget['title']) ?>
                                            </h6>
                                            <p class="card-text small text-muted">
                                                <?= htmlspecialchars($widget['description']) ?>
                                            </p>

                                            <!-- Отображение источника -->
                                            <div class="mt-2">
                                                <?php if (($widget['source'] ?? 'system') === 'system'): ?>
                                                    <span class="badge bg-info">
                                <i class="bi bi-gear"></i> Системный
                            </span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                <i class="bi bi-plug"></i>
                                <?= htmlspecialchars(($widget['plugin_name'] ?? 'Плагин')) ?>
                            </span>
                                                <?php endif; ?>

                                                <span class="badge bg-secondary ms-1">
                            <i class="bi bi-<?= $widget['size'] === 'small' ? 'box' :
                                ($widget['size'] === 'medium' ? 'box-arrow-in-up' : 'box-arrow-up') ?>"></i>
                            <?= $widget['size'] === 'small' ? 'Маленький' :
                                ($widget['size'] === 'medium' ? 'Средний' : 'Большой') ?>
                        </span>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input widget-toggle-switch"
                                                   type="checkbox"
                                                   data-widget-id="<?= htmlspecialchars($widget['id']) ?>"
                                                   id="widget-<?= htmlspecialchars($widget['id']) ?>"
                                                <?= isset($_SESSION['user_widgets'][$widget['id']]) && $_SESSION['user_widgets'][$widget['id']] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="widget-<?= htmlspecialchars($widget['id']) ?>"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary save-widgets">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Глобальная переменная для отслеживания изменений виджетов
    let widgetChanges = {};
    window.userWidgetsState = {};
    let isLoading = false;

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {

        // Загружаем скрытые виджеты при загрузке страницы
        loadHiddenWidgets();
        loadWidgetsState();

        // При открытии модального окна сбрасываем изменения
        document.getElementById('widgetsModal')?.addEventListener('show.bs.modal', function() {
            resetWidgetChanges();
        });

        // При закрытии модального окна сбрасываем изменения
        document.getElementById('widgetsModal')?.addEventListener('hidden.bs.modal', function() {
            resetWidgetChanges();
        });

        // Обработчик для кнопки сохранения настроек виджетов
        document.querySelector('.save-widgets')?.addEventListener('click', function(e) {
            e.preventDefault();
            saveWidgetSettings();
        });

        // Обработчик для кнопки скрытия панели восстановления
        document.getElementById('hideRestorePanel')?.addEventListener('click', function() {
            document.getElementById('hiddenWidgetsPanel').style.display = 'none';
        });

        // Загружаем информацию о виджетах для модального окна
        loadWidgetsForModal();
    });

    // Загрузка состояния виджетов
    function loadWidgetsState() {
        // Пытаемся получить состояние из localStorage или инициализируем пустым объектом
        try {
            const savedState = localStorage.getItem('userWidgetsState');
            if (savedState) {
                window.userWidgetsState = JSON.parse(savedState);
            }
        } catch (e) {
            console.error('Error loading widgets state:', e);
        }
    }

    // Сохранение состояния виджетов
    function saveWidgetsState() {
        try {
            localStorage.setItem('userWidgetsState', JSON.stringify(window.userWidgetsState));
        } catch (e) {
            console.error('Error saving widgets state:', e);
        }
    }

    // Сбрасываем накопленные изменения виджетов
    function resetWidgetChanges() {
        widgetChanges = {};

        // Убираем визуальные индикаторы изменений
        document.querySelectorAll('.widget-toggle-switch').forEach(checkbox => {
            checkbox.classList.remove('border-primary', 'border-danger');
            checkbox.style.boxShadow = '';
        });
    }

    // Собираем изменения при переключении виджетов в модальном окне
    document.addEventListener('change', function(event) {
        if (event.target.classList.contains('widget-toggle-switch')) {
            const checkbox = event.target;
            const widgetId = checkbox.dataset.widgetId;
            const isEnabled = checkbox.checked;

            // Сохраняем изменение
            widgetChanges[widgetId] = isEnabled;

            // Визуально показываем, что есть несохраненные изменения
            if (isEnabled) {
                checkbox.classList.add('border-primary');
                checkbox.style.boxShadow = '0 0 0 2px rgba(13, 110, 253, 0.25)';
            } else {
                checkbox.classList.add('border-danger');
                checkbox.style.boxShadow = '0 0 0 2px rgba(220, 53, 69, 0.25)';
            }
        }
    });

    // Функция сохранения настроек виджетов
    function saveWidgetSettings() {
        if (isLoading) return;

        // Если нет изменений, просто закрываем модальное окно
        if (Object.keys(widgetChanges).length === 0) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('widgetsModal'));
            modal?.hide();
            showNotification('Изменений не было', 'info');
            return;
        }

        // Собираем все состояния виджетов
        const allWidgets = {};
        document.querySelectorAll('.widget-toggle-switch').forEach(checkbox => {
            const widgetId = checkbox.dataset.widgetId;
            // Используем измененное состояние, если оно есть
            if (widgetChanges.hasOwnProperty(widgetId)) {
                allWidgets[widgetId] = widgetChanges[widgetId];
            } else {
                // Иначе используем текущее состояние переключателя
                allWidgets[widgetId] = checkbox.checked;
            }
        });

        isLoading = true;

        const formData = new FormData();
        formData.append('action', 'save_widgets');
        formData.append('widgets', JSON.stringify(allWidgets));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '<?= csrf_token() ?>');

        // Блокируем кнопку сохранения
        const saveButton = document.querySelector('.save-widgets');
        const originalText = saveButton.innerHTML;
        saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Сохранение...';
        saveButton.disabled = true;

        fetch('/admin/save-widgets', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Закрываем модальное окно
                    const modal = bootstrap.Modal.getInstance(document.getElementById('widgetsModal'));
                    modal?.hide();

                    // Сбрасываем изменения
                    resetWidgetChanges();

                    // Обновляем дашборд без перезагрузки
                    updateDashboardAfterSave(allWidgets);

                    showNotification('Настройки виджетов сохранены', 'success');
                } else {
                    showNotification(data.error || 'Ошибка при сохранении', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сети', 'error');
            })
            .finally(() => {
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
                isLoading = false;
            });
    }

    // Обновление дашборда после сохранения настроек
    function updateDashboardAfterSave(widgetsState) {
        const widgetsGrid = document.querySelector('.dashboard-widgets-grid');
        if (!widgetsGrid) return;

        // 1. Скрываем виджеты, которые выключены
        document.querySelectorAll('.dashboard-widget').forEach(widget => {
            const widgetId = widget.dataset.widgetId;
            if (!widgetsState[widgetId]) {
                // Анимация скрытия
                widget.style.opacity = '0.5';
                widget.style.transform = 'scale(0.95)';
                widget.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

                setTimeout(() => {
                    widget.style.display = 'none';

                    // Добавляем в панель скрытых, если его там нет
                    const hiddenItem = document.querySelector(`.hidden-widget-item[data-widget-id="${widgetId}"]`);
                    if (!hiddenItem) {
                        const widgetTitle = widget.querySelector('.widget-title h5')?.textContent || widgetId;
                        const widgetIcon = widget.querySelector('.widget-title .bi')?.className || 'bi-question-circle';

                        addToHiddenWidgetsList(widgetId, widgetTitle, widgetIcon);
                    }
                }, 300);
            }
        });

        // 2. Показываем виджеты, которые включены
        setTimeout(() => {
            for (const widgetId in widgetsState) {
                if (widgetsState[widgetId]) {
                    // Проверяем, есть ли виджет на дашборде
                    const existingWidget = document.querySelector(`.dashboard-widget[data-widget-id="${widgetId}"]`);
                    if (!existingWidget) {
                        // Загружаем и добавляем виджет
                        addWidgetToDashboard(widgetId);
                    } else {
                        // Показываем существующий виджет
                        existingWidget.style.display = '';
                        existingWidget.style.opacity = '0';
                        existingWidget.style.transform = 'translateY(20px)';

                        setTimeout(() => {
                            existingWidget.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            existingWidget.style.opacity = '1';
                            existingWidget.style.transform = 'translateY(0)';
                        }, 10);
                    }

                    // Удаляем из панели скрытых, если он там есть
                    const hiddenItem = document.querySelector(`.hidden-widget-item[data-widget-id="${widgetId}"]`);
                    if (hiddenItem) {
                        hiddenItem.remove();
                    }
                }
            }

            // Проверяем, нужно ли показывать панель скрытых
            checkHiddenWidgetsPanel();
        }, 350);
    }

    // Загрузка виджетов для модального окна
    function loadWidgetsForModal() {
        // Здесь можно загрузить дополнительную информацию о виджетах
        // если нужно обновлять их динамически
    }

    // Функция добавления виджета на дашборд
    function addWidgetToDashboard(widgetId) {
        // Проверяем, нет ли уже такого виджета на дашборде
        const existingWidget = document.querySelector(`.dashboard-widget[data-widget-id="${widgetId}"]`);
        if (existingWidget) {
            return; // Виджет уже есть
        }

        // Запрашиваем HTML виджета с сервера
        fetch(`/admin/widget-html/${widgetId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.html) {
                    // Добавляем виджет в сетку
                    const widgetsGrid = document.querySelector('.dashboard-widgets-grid');
                    if (widgetsGrid) {
                        // Создаем временный контейнер для парсинга HTML
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html.trim();
                        const widgetElement = tempDiv.firstChild;

                        // Добавляем в сетку
                        widgetsGrid.appendChild(widgetElement);

                        // Добавляем обработчик для кнопки скрытия
                        const toggleButton = widgetElement.querySelector('.widget-toggle');
                        if (toggleButton) {
                            toggleButton.addEventListener('click', function() {
                                const widgetId = this.dataset.widgetId;
                                const widget = this.closest('.dashboard-widget');
                                const widgetTitle = widget.querySelector('.widget-title h5').textContent;

                                if (confirm(`Скрыть виджет "${widgetTitle}"?`)) {
                                    hideWidget(widgetId, widget);
                                }
                            });
                        }

                        // Анимация появления
                        widgetElement.style.opacity = '0';
                        widgetElement.style.transform = 'translateY(20px)';

                        setTimeout(() => {
                            widgetElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                            widgetElement.style.opacity = '1';
                            widgetElement.style.transform = 'translateY(0)';
                        }, 10);
                    }
                }
            })
            .catch(error => {
                console.error('Error loading widget HTML:', error);
                showNotification('Ошибка загрузки виджета', 'error');
            });
    }

    // Функция скрытия виджета через кнопку на виджете
    function hideWidget(widgetId, widgetElement) {
        if (isLoading) return;

        const widgetTitle = widgetElement.querySelector('.widget-title h5')?.textContent || widgetId;

        if (!confirm(`Скрыть виджет "${widgetTitle}"?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('widget_id', widgetId);
        formData.append('action', 'hide_widget');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '<?= csrf_token() ?>');


        // Показываем индикатор загрузки
        const toggleButton = widgetElement.querySelector('.widget-toggle');
        const originalIcon = toggleButton?.innerHTML;
        if (toggleButton) {
            toggleButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            toggleButton.disabled = true;
        }

        fetch('/admin/toggle-widget', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Анимация скрытия
                    widgetElement.style.opacity = '0.5';
                    widgetElement.style.transform = 'scale(0.95)';

                    setTimeout(() => {
                        widgetElement.style.display = 'none';

                        // Добавляем в панель скрытых
                        const widgetIcon = widgetElement.querySelector('.widget-title .bi')?.className || 'bi-question-circle';
                        addToHiddenWidgetsList(widgetId, widgetTitle, widgetIcon);

                        // Обновляем переключатель в модальном окне
                        const checkbox = document.querySelector(`.widget-toggle-switch[data-widget-id="${widgetId}"]`);
                        if (checkbox) {
                            checkbox.checked = false;
                        }

                        showNotification(`Виджет "${widgetTitle}" скрыт`, 'success');
                        showRestorePanel();
                    }, 300);
                } else {
                    showNotification(data.error || 'Ошибка при скрытии виджета', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сети', 'error');
            })
            .finally(() => {
                if (toggleButton) {
                    toggleButton.innerHTML = originalIcon;
                    toggleButton.disabled = false;
                }
            });
    }

    // Функция восстановления виджета
    function restoreWidget(widgetId, widgetElement = null) {
        if (isLoading) return;

        console.log('Начинаем восстановление виджета:', widgetId);

        // СНАЧАЛА проверяем, есть ли виджет в DOM (даже скрытый)
        const existingWidget = document.querySelector(`.dashboard-widget[data-widget-id="${widgetId}"]`);

        if (existingWidget) {
            console.log('Виджет уже существует в DOM');

            // Если виджет скрыт, просто показываем его
            if (existingWidget.style.display === 'none' ||
                window.getComputedStyle(existingWidget).display === 'none') {
                console.log('Виджет скрыт, показываем его');

                // Отправляем запрос на сервер для обновления состояния
                const formData = new FormData();
                formData.append('widget_id', widgetId);
                formData.append('action', 'show_widget');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '<?= csrf_token() ?>');

                fetch('/admin/toggle-widget', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Показываем виджет
                            existingWidget.style.display = '';
                            existingWidget.style.opacity = '0';
                            existingWidget.style.transform = 'translateY(20px)';

                            setTimeout(() => {
                                existingWidget.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                                existingWidget.style.opacity = '1';
                                existingWidget.style.transform = 'translateY(0)';
                            }, 10);

                            // Удаляем из списка скрытых
                            removeFromHiddenList(widgetId);
                            showNotification('Виджет восстановлен', 'success');
                        }
                    });
                return;
            }
        }

        // Если виджета нет или он не скрыт, продолжаем обычный процесс восстановления
        const formData = new FormData();
        formData.append('widget_id', widgetId);
        formData.append('action', 'show_widget');
        formData.append('_token', '<?= csrf_token() ?>');

        // Показываем индикатор загрузки
        const restoreBtn = document.querySelector(`.restore-widget-btn[data-widget-id="${widgetId}"]`);
        const originalText = restoreBtn?.innerHTML;
        if (restoreBtn) {
            restoreBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            restoreBtn.disabled = true;
        }

        fetch('/admin/toggle-widget', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log('Ответ от сервера:', data);

                if (data.success) {
                    if (data.html) {
                        console.log('HTML получен, длина:', data.html.length);
                        addWidgetToDashboardDirectly(widgetId, data.html);
                    } else {
                        console.log('HTML не получен, запрашиваем отдельно');
                        fetchWidgetHtmlAndAdd(widgetId);
                    }

                    // Удаляем из списка скрытых
                    removeFromHiddenList(widgetId);

                    // Обновляем переключатель в модальном окне
                    updateModalSwitch(widgetId, true);

                    showNotification('Виджет восстановлен', 'success');

                    // Проверяем панель скрытых
                    checkHiddenWidgetsPanel();
                } else {
                    showNotification(data.error || 'Ошибка при восстановлении', 'error');
                    if (restoreBtn) {
                        restoreBtn.innerHTML = originalText;
                        restoreBtn.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Ошибка сети', 'error');
                if (restoreBtn) {
                    restoreBtn.innerHTML = originalText;
                    restoreBtn.disabled = false;
                }
            });
    }

    // Обновление состояния виджета на клиенте
    function updateWidgetState(widgetId, isEnabled) {
        window.userWidgetsState[widgetId] = isEnabled;
        saveWidgetsState();

        // Обновляем переключатель в модальном окне
        const checkbox = document.querySelector(`.widget-toggle-switch[data-widget-id="${widgetId}"]`);
        if (checkbox) {
            checkbox.checked = isEnabled;
        }
    }

    // Функция загрузки HTML виджета и добавления на дашборд
    function fetchWidgetHtmlAndAdd(widgetId) {
        console.log('Запрашиваем HTML виджета:', widgetId);

        fetch(`/admin/widget-html/${widgetId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.html) {
                    console.log('HTML получен через отдельный запрос');
                    addWidgetToDashboardDirectly(widgetId, data.html);
                } else {
                    console.error('Не удалось получить HTML виджета');
                    showNotification('Не удалось загрузить виджет', 'error');
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки HTML:', error);
                showNotification('Ошибка загрузки виджета', 'error');
            });
    }

    // Функция добавления виджета на дашборд напрямую с HTML
    function addWidgetToDashboardDirectly(widgetId, html) {
        console.log('Добавление виджета:', widgetId);

        // ПЕРВОЕ: Ищем ВСЕ существующие виджеты с таким ID
        const existingWidgets = document.querySelectorAll(`.dashboard-widget[data-widget-id="${widgetId}"]`);

        // Если есть скрытые виджеты (display: none)
        existingWidgets.forEach(existingWidget => {
            if (existingWidget.style.display === 'none' ||
                window.getComputedStyle(existingWidget).display === 'none') {
                console.log('Найден скрытый виджет, показываем его');

                // Просто показываем существующий виджет
                existingWidget.style.display = '';
                existingWidget.style.opacity = '0';
                existingWidget.style.transform = 'translateY(20px)';

                // Анимация появления
                setTimeout(() => {
                    existingWidget.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    existingWidget.style.opacity = '1';
                    existingWidget.style.transform = 'translateY(0)';
                    console.log('Скрытый виджет показан');
                }, 10);

                // Выходим - виджет уже есть
                return;
            }
        });

        // Если виджета вообще нет (даже скрытого)
        const widgetsGrid = document.querySelector('.dashboard-widgets-grid');
        if (!widgetsGrid) {
            console.error('Сетка виджетов не найдена!');
            return;
        }

        // Проверяем, есть ли уже видимый виджет
        const visibleWidget = document.querySelector(`.dashboard-widget[data-widget-id="${widgetId}"]:not([style*="display: none"])`);
        if (visibleWidget) {
            console.log('Виджет уже видим на дашборде');
            return;
        }

        console.log('Создаем новый виджет...');

        // Используем insertAdjacentHTML для добавления
        widgetsGrid.insertAdjacentHTML('beforeend', html);

        // Находим только что добавленный виджет
        const widgetElements = document.querySelectorAll(`.dashboard-widget[data-widget-id="${widgetId}"]`);
        const widgetElement = widgetElements[widgetElements.length - 1];

        if (!widgetElement) {
            console.error('Не удалось найти добавленный виджет');
            return;
        }

        console.log('Виджет добавлен в DOM:', widgetElement);

        // Добавляем обработчик для кнопки скрытия
        const toggleButton = widgetElement.querySelector('.widget-toggle');
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                const widgetId = this.dataset.widgetId;
                const widget = this.closest('.dashboard-widget');
                const widgetTitle = widget.querySelector('.widget-title h5')?.textContent || widgetId;

                if (confirm(`Скрыть виджет "${widgetTitle}"?`)) {
                    hideWidget(widgetId, widget);
                }
            });
        }

        // Анимация появления
        widgetElement.style.opacity = '0';
        widgetElement.style.transform = 'translateY(20px)';

        setTimeout(() => {
            widgetElement.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            widgetElement.style.opacity = '1';
            widgetElement.style.transform = 'translateY(0)';
            console.log('Анимация завершена');
        }, 10);
    }

    // Функция добавления в список скрытых виджетов
    function addToHiddenWidgetsList(widgetId, widgetTitle, widgetIcon) {
        const panel = document.getElementById('hiddenWidgetsList');
        if (!panel) return;

        // Проверяем, не добавлен ли уже этот виджет
        const existingItem = panel.querySelector(`[data-widget-id="${widgetId}"]`);
        if (existingItem) {
            return;
        }

        const widgetItem = document.createElement('div');
        widgetItem.className = 'hidden-widget-item';
        widgetItem.setAttribute('data-widget-id', widgetId);

        const widgetInfo = {
            id: widgetId,
            title: widgetTitle,
            icon: widgetIcon.replace('bi ', ''),
            description: 'Скрытый виджет'
        };

        widgetItem.innerHTML = `
        <div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi ${widgetIcon}"></i>
                <div>
                    <strong>${widgetTitle}</strong>
                    <div class="small text-muted">${widgetId.replace('_', ' ')}</div>
                </div>
            </div>
            <button class="btn btn-sm btn-success restore-widget-btn"
                    data-widget-id="${widgetId}"
                    data-widget-info='${JSON.stringify(widgetInfo).replace(/'/g, "&apos;")}'>
                <i class="bi bi-eye"></i> Восстановить
            </button>
        </div>
    `;

        panel.appendChild(widgetItem);

        // Добавляем обработчик для кнопки восстановления
        const restoreBtn = widgetItem.querySelector('.restore-widget-btn');
        restoreBtn.addEventListener('click', function() {
            const widgetId = this.dataset.widgetId;
            const widgetInfo = JSON.parse(this.dataset.widgetInfo);
            restoreWidget(widgetId, widgetInfo);
        });

        // Показываем панель восстановления
        document.getElementById('hiddenWidgetsPanel').style.display = 'block';
    }

    // Функция удаления из списка скрытых
    function removeFromHiddenList(widgetId) {
        console.log('Удаляем виджет из скрытых:', widgetId);

        const hiddenItems = document.querySelectorAll(`.hidden-widget-item[data-widget-id="${widgetId}"]`);

        hiddenItems.forEach(item => {
            console.log('Удаляем элемент:', item);
            item.remove();
        });

        checkHiddenWidgetsPanel();
    }

    // Проверяем, нужно ли показывать панель скрытых виджетов
    function checkHiddenWidgetsPanel() {
        const hiddenWidgetsList = document.getElementById('hiddenWidgetsList');
        const hiddenWidgetsPanel = document.getElementById('hiddenWidgetsPanel');

        if (hiddenWidgetsList && hiddenWidgetsPanel) {
            const hiddenItems = hiddenWidgetsList.querySelectorAll('.hidden-widget-item');
            console.log('Скрытых виджетов осталось:', hiddenItems.length);

            if (hiddenItems.length > 0) {
                hiddenWidgetsPanel.style.display = 'block';
            } else {
                hiddenWidgetsPanel.style.display = 'none';
            }
        }
    }

    // Показать панель восстановления
    function showRestorePanel() {
        const panel = document.getElementById('hiddenWidgetsPanel');
        if (panel) {
            panel.style.display = 'block';
            panel.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Обновление переключателя в модальном окне
    function updateModalSwitch(widgetId, enabled) {
        const switchElements = document.querySelectorAll(`.widget-toggle-switch[data-widget-id="${widgetId}"]`);

        switchElements.forEach(switchElement => {
            switchElement.checked = enabled;
        });

        console.log('Переключатель обновлен для виджета:', widgetId, enabled);
    }

    // Загрузка скрытых виджетов при старте
    function loadHiddenWidgets() {
        console.log('Загружаем скрытые виджеты...');

        fetch('/admin/get-hidden-widgets')
            .then(response => response.json())
            .then(data => {
                console.log('Данные скрытых виджетов:', data);

                if (data.success && data.hidden_widgets && data.hidden_widgets.length > 0) {
                    const panel = document.getElementById('hiddenWidgetsList');
                    if (panel) {
                        panel.innerHTML = '';

                        data.hidden_widgets.forEach(widgetInfo => {
                            const widgetItem = document.createElement('div');
                            widgetItem.className = 'hidden-widget-item';
                            widgetItem.setAttribute('data-widget-id', widgetInfo.id);
                            widgetItem.innerHTML = `
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi ${widgetInfo.icon}"></i>
                                    <div>
                                        <strong>${widgetInfo.title}</strong>
                                        <div class="small text-muted">${widgetInfo.description}</div>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-success restore-widget-btn"
                                        data-widget-id="${widgetInfo.id}">
                                    <i class="bi bi-eye"></i> Восстановить
                                </button>
                            </div>
                        `;
                            panel.appendChild(widgetItem);

                            console.log('Добавлен скрытый виджет в панель:', widgetInfo.id);
                        });

                        document.getElementById('hiddenWidgetsPanel').style.display = 'block';
                        console.log('Панель скрытых виджетов показана');
                    }
                } else {
                    document.getElementById('hiddenWidgetsPanel').style.display = 'none';
                    console.log('Нет скрытых виджетов, панель скрыта');
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки скрытых виджетов:', error);
                document.getElementById('hiddenWidgetsPanel').style.display = 'none';
            });
    }

    // Показываем уведомление
    function showNotification(message, type = 'info') {
        // Определяем классы для разных типов уведомлений
        const alertClass = type === 'success' ? 'alert-success' :
            type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

        // Создаем элемент уведомления
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
        notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

        // Добавляем в DOM
        document.body.appendChild(notification);

        // Автоматически скрываем через 4 секунды
        setTimeout(() => {
            if (notification.parentNode) {
                try {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(notification);
                    bsAlert.close();
                } catch (e) {
                    notification.remove();
                }
            }
        }, 4000);
    }

    // Делегирование событий для динамически созданных элементов
    document.addEventListener('click', function(event) {
        // Обработка кнопок восстановления
        if (event.target.closest('.restore-widget-btn')) {
            event.preventDefault();
            const button = event.target.closest('.restore-widget-btn');
            const widgetId = button.dataset.widgetId;
            console.log('Нажата кнопка восстановления для:', widgetId);
            restoreWidget(widgetId);
        }

        // Обработка кнопок скрытия
        if (event.target.closest('.widget-toggle')) {
            event.preventDefault();
            const button = event.target.closest('.widget-toggle');
            const widgetId = button.dataset.widgetId;
            const widget = button.closest('.dashboard-widget');
            const widgetTitle = widget.querySelector('.widget-title h5')?.textContent || widgetId;

            if (confirm(`Скрыть виджет "${widgetTitle}"?`)) {
                hideWidget(widgetId, widget);
            }
        }
    });

    // Обработка закрытия алертов через Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Дашборд загружен, инициализация...');

        // Загружаем скрытые виджеты
        loadHiddenWidgets();

        // Инициализируем состояние
        if (!window.userWidgetsState) {
            window.userWidgetsState = {};
        }

        console.log('Инициализация завершена');
    });
</script>
</body>
</html>