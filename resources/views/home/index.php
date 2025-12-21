<?php
// Переменные для layout
$title = $title ?? 'Главная страница MVC системы';
$content = ''; // Будем накапливать контент

ob_start(); // Начинаем буферизацию вывода
?>
    <!-- Герой-секция -->
    <div class="jumbotron text-center">
        <h1 class="display-4 fw-bold mb-3">Добро пожаловать в MVC System</h1>
        <p class="lead mb-4">
            Мощная модульная система управления контентом с поддержкой плагинов, виджетов и современным интерфейсом.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/admin" class="btn btn-light btn-lg px-4">
                    <i class="bi bi-speedometer2 me-2"></i> Панель управления
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn-light btn-lg px-4">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Войти в систему
                </a>
                <a href="/quick-login" class="btn btn-outline-light btn-lg px-4">
                    <i class="bi bi-lightning me-2"></i> Быстрый вход
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Демо-блок -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-info-circle fs-3"></i>
            </div>
            <div>
                <h4 class="alert-heading mb-2">Демонстрация системы</h4>
                <p class="mb-1">Для тестирования административной панели используйте:</p>
                <ul class="mb-0">
                    <li><strong>Логин:</strong> admin</li>
                    <li><strong>Пароль:</strong> admin</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Возможности системы -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">
                <i class="bi bi-stars text-primary me-2"></i>
                Основные возможности
            </h2>
        </div>

        <?php
        $features = $features ?? [
                'Модульная архитектура MVC' => 'bi-diagram-3',
                'Поддержка плагинов' => 'bi-plug',
                'Система виджетов' => 'bi-grid-3x3-gap',
                'Современный интерфейс' => 'bi-palette',
                'Безопасная аутентификация' => 'bi-shield-check',
                'Гибкая маршрутизация' => 'bi-signpost-split',
                'База данных' => 'bi-database',
                'RESTful API' => 'bi-code-slash'
            ];

        foreach ($features as $feature => $icon):
            ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="feature-card p-4 text-center h-100">
                    <div class="feature-icon">
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-3"><?= htmlspecialchars($feature) ?></h5>
                    <p class="text-muted small mb-0">
                        <?php
                        $descriptions = [
                            'Модульная архитектура MVC' => 'Четкое разделение ответственности между компонентами',
                            'Поддержка плагинов' => 'Легко расширяемая система с помощью плагинов',
                            'Система виджетов' => 'Гибкая настройка интерфейса с помощью виджетов',
                            'Современный интерфейс' => 'Адаптивный дизайн на Bootstrap 5',
                            'Безопасная аутентификация' => 'Защищенная система входа и управления сессиями',
                            'Гибкая маршрутизация' => 'Мощная система маршрутизации с параметрами',
                            'База данных' => 'Интеграция с MySQL через PDO',
                            'RESTful API' => 'Готовое API для интеграции с другими системами'
                        ];
                        echo htmlspecialchars($descriptions[$feature] ?? 'Описание возможности');
                        ?>
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Статистика системы -->
    <div class="row mb-5">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">
                        <i class="bi bi-graph-up text-primary me-2"></i>
                        Статистика системы
                    </h3>
                    <div class="row text-center">
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h2 class="text-primary mb-1">1</h2>
                                <small class="text-muted">Пользователей</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h2 class="text-success mb-1">0</h2>
                                <small class="text-muted">Плагинов</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h2 class="text-warning mb-1">4</h2>
                                <small class="text-muted">Виджетов</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h2 class="text-info mb-1">8.3</h2>
                                <small class="text-muted">PHP версия</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$content = ob_get_clean(); // Получаем буферизированный вывод

// Подключаем layout
include __DIR__ . '/../layouts/default.php';