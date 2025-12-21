<?php

namespace Plugins\WidgetsPlugin;

use Core\Widgets\WidgetManager;

class Plugin
{
    private $widgets = [];

    public function activate()
    {
        error_log("WidgetsPlugin: Активация плагина");
        $this->registerWidgets();
    }

    public function deactivate()
    {
        error_log("WidgetsPlugin: Деактивация плагина");
        $this->unregisterWidgets();
    }

    public function init()
    {
        error_log("WidgetsPlugin: Инициализация плагина");
        $this->registerWidgets();
    }

    public function registerRoutes($router)
    {
        error_log("WidgetsPlugin: Регистрация маршрутов");

        // API маршруты для виджетов
        $router->get('/api/widgets/weather', function() {
            header('Content-Type: application/json');

            $weatherData = [
                'temperature' => '+18°C',
                'condition' => 'Солнечно',
                'humidity' => '45%',
                'wind' => '3 м/с',
                'pressure' => '760 мм',
                'location' => 'Москва',
                'forecast' => [
                    ['day' => 'Завтра', 'high' => '+20°C', 'low' => '+12°C', 'condition' => 'облачно'],
                    ['day' => 'Ср', 'high' => '+22°C', 'low' => '+14°C', 'condition' => 'солнечно'],
                    ['day' => 'Чт', 'high' => '+19°C', 'low' => '+11°C', 'condition' => 'дождь']
                ]
            ];

            echo json_encode([
                'success' => true,
                'data' => $weatherData,
                'timestamp' => time()
            ]);
        });

        $router->get('/api/widgets/comments', function() {
            header('Content-Type: application/json');

            $comments = [
                [
                    'id' => 1,
                    'author' => 'Иван Петров',
                    'avatar' => 'IP',
                    'content' => 'Отличная статья, спасибо!',
                    'time' => '2 часа назад',
                    'post' => 'Введение в MVC'
                ],
                [
                    'id' => 2,
                    'author' => 'Мария Сидорова',
                    'avatar' => 'МС',
                    'content' => 'Было бы здорово добавить больше примеров.',
                    'time' => '5 часов назад',
                    'post' => 'Работа с базой данных'
                ],
                [
                    'id' => 3,
                    'author' => 'Алексей Иванов',
                    'avatar' => 'АИ',
                    'content' => 'Понравилась система плагинов.',
                    'time' => 'вчера',
                    'post' => 'Разработка плагинов'
                ]
            ];

            echo json_encode([
                'success' => true,
                'data' => $comments,
                'count' => count($comments)
            ]);
        });

        $router->get('/api/widgets/system', function() {
            header('Content-Type: application/json');

            // Получаем системную информацию
            $cpuUsage = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
            $memoryUsage = round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB';

            // Исправление: явное преобразование к float для избежания предупреждений
            $freeBytes = disk_free_space("/");
            $totalBytes = disk_total_space("/");
            $freeGB = round((float)$freeBytes / 1024 / 1024 / 1024, 2) . ' GB';
            $totalGB = round((float)$totalBytes / 1024 / 1024 / 1024, 2) . ' GB';

            // Вычисление процента использования диска
            $usedPercent = 0;
            if ($totalBytes > 0) {
                $usedPercent = round((1 - ((float)$freeBytes / (float)$totalBytes)) * 100, 1);
            }

            $systemData = [
                'cpu' => [
                    'usage' => $cpuUsage,
                    'status' => 'normal'
                ],
                'memory' => [
                    'usage' => $memoryUsage,
                    'status' => 'normal'
                ],
                'disk' => [
                    'free' => $freeGB,
                    'total' => $totalGB,
                    'used_percent' => $usedPercent
                ],
                'php' => [
                    'version' => phpversion(),
                    'extensions' => count(get_loaded_extensions())
                ],
                'uptime' => $this->getSystemUptime(),
                'last_update' => date('d.m.Y H:i:s')
            ];

            echo json_encode([
                'success' => true,
                'data' => $systemData
            ]);
        });
    }

    private function getSystemUptime()
    {
        // Для Linux
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $uptime = explode(' ', $uptime)[0];

            // Исправление: используем fmod() для чисел с плавающей точкой
            $uptimeSeconds = (float)$uptime;
            $days = floor($uptimeSeconds / 86400);
            // Используем fmod() вместо оператора % для чисел с плавающей точкой
            $hours = floor(fmod($uptimeSeconds, 86400) / 3600);
            $minutes = floor(fmod($uptimeSeconds, 3600) / 60);

            return "{$days}д {$hours}ч {$minutes}м";
        }

        // Для Windows (если нужно)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows не поддерживает /proc/uptime
            return "Недоступно (Windows)";
        }

        return "Недоступно";
    }

    private function registerWidgets()
    {
        error_log("WidgetsPlugin: Регистрация виджетов");

        $widgetManager = WidgetManager::getInstance();

        // 1. Виджет погоды
        $widgetManager->registerWidget([
            'id' => 'weather_widget',
            'title' => 'Погода',
            'description' => 'Текущая погода и прогноз',
            'icon' => 'bi-cloud-sun',
            'size' => 'medium',
            'source' => 'plugin',
            'plugin_name' => 'WidgetsPlugin',
            'content' => function() {
                return $this->renderWeatherWidget();
            }
        ]);

        // 2. Виджет последних комментариев
        $widgetManager->registerWidget([
            'id' => 'recent_comments_widget',
            'title' => 'Последние комментарии',
            'description' => 'Свежие комментарии пользователей',
            'icon' => 'bi-chat-left-text',
            'size' => 'large',
            'source' => 'plugin',
            'plugin_name' => 'WidgetsPlugin',
            'content' => function() {
                return $this->renderRecentCommentsWidget();
            }
        ]);

        // 3. Виджет мониторинга системы
        $widgetManager->registerWidget([
            'id' => 'system_monitor_widget',
            'title' => 'Монитор системы',
            'description' => 'Состояние сервера и ресурсов',
            'icon' => 'bi-graph-up',
            'size' => 'medium',
            'source' => 'plugin',
            'plugin_name' => 'WidgetsPlugin',
            'content' => function() {
                return $this->renderSystemMonitorWidget();
            }
        ]);

        error_log("WidgetsPlugin: Виджеты зарегистрированы");
    }

    private function unregisterWidgets()
    {
        $widgetManager = WidgetManager::getInstance();

        $widgets = ['weather_widget', 'recent_comments_widget', 'system_monitor_widget'];

        foreach ($widgets as $widgetId) {
            $widgetManager->unregisterWidget($widgetId);
        }

        error_log("WidgetsPlugin: Виджеты удалены");
    }

    private function renderWeatherWidget()
    {
        ob_start();
        ?>
        <div class="weather-widget">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <div class="weather-icon me-3">
                        <i class="bi bi-cloud-sun fs-1 text-warning"></i>
                    </div>
                    <div class="temperature">
                        <h3 class="mb-0">+18°C</h3>
                        <div class="text-muted small">Солнечно</div>
                    </div>
                </div>
                <div class="location">
                    <i class="bi bi-geo-alt"></i> Москва
                </div>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="border rounded p-2 text-center">
                        <small class="text-muted d-block">Влажность</small>
                        <strong>45%</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-2 text-center">
                        <small class="text-muted d-block">Ветер</small>
                        <strong>3 м/с</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border rounded p-2 text-center">
                        <small class="text-muted d-block">Давление</small>
                        <strong>760 мм</strong>
                    </div>
                </div>
            </div>

            <div class="weather-forecast">
                <h6 class="border-bottom pb-2 mb-2">Прогноз на 3 дня:</h6>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="py-2">
                            <div class="fw-bold">Завтра</div>
                            <div class="text-warning">+20°/+12°</div>
                            <div class="text-muted small">Облачно</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="py-2">
                            <div class="fw-bold">Ср</div>
                            <div class="text-warning">+22°/+14°</div>
                            <div class="text-muted small">Солнечно</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="py-2">
                            <div class="fw-bold">Чт</div>
                            <div class="text-warning">+19°/+11°</div>
                            <div class="text-muted small">Дождь</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-sm btn-outline-primary w-100 refresh-weather" data-widget-id="weather_widget">
                    <i class="bi bi-arrow-clockwise"></i> Обновить
                </button>
            </div>
        </div>
        <script>
            document.querySelector('.refresh-weather')?.addEventListener('click', function() {
                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Обновление...';
                btn.disabled = true;

                fetch('/api/widgets/weather')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Здесь можно обновить данные виджета
                            console.log('Данные погоды обновлены:', data);

                            // Пример обновления температуры
                            const tempElement = btn.closest('.weather-widget').querySelector('.temperature h3');
                            if (tempElement && data.data.temperature) {
                                tempElement.textContent = data.data.temperature;
                            }

                            // Показать уведомление
                            showNotification('Данные погоды обновлены', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка обновления погоды:', error);
                        showNotification('Ошибка обновления', 'error');
                    })
                    .finally(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    private function renderRecentCommentsWidget()
    {
        ob_start();
        ?>
        <div class="recent-comments-widget">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Последние комментарии</h6>
                <span class="badge bg-primary">3 новых</span>
            </div>

            <div class="comment-list">
                <!-- Комментарий 1 -->
                <div class="comment-item d-flex align-items-start mb-3 p-2 border rounded">
                    <div class="avatar-circle d-flex align-items-center justify-content-center bg-primary text-white me-3">
                        ИП
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Иван Петров</strong>
                                <div class="text-muted small">2 часа назад</div>
                            </div>
                            <span class="badge bg-light text-dark small">Введение в MVC</span>
                        </div>
                        <p class="mb-0 mt-1">Отличная статья, спасибо! Буду ждать продолжения.</p>
                        <div class="comment-actions mt-2">
                            <a href="#" class="text-decoration-none me-3"><i class="bi bi-reply"></i> Ответить</a>
                            <a href="#" class="text-decoration-none"><i class="bi bi-trash"></i> Удалить</a>
                        </div>
                    </div>
                </div>

                <!-- Комментарий 2 -->
                <div class="comment-item d-flex align-items-start mb-3 p-2 border rounded">
                    <div class="avatar-circle d-flex align-items-center justify-content-center bg-success text-white me-3">
                        МС
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Мария Сидорова</strong>
                                <div class="text-muted small">5 часов назад</div>
                            </div>
                            <span class="badge bg-light text-dark small">Работа с БД</span>
                        </div>
                        <p class="mb-0 mt-1">Было бы здорово добавить больше примеров работы с транзакциями.</p>
                        <div class="comment-actions mt-2">
                            <a href="#" class="text-decoration-none me-3"><i class="bi bi-reply"></i> Ответить</a>
                            <a href="#" class="text-decoration-none"><i class="bi bi-trash"></i> Удалить</a>
                        </div>
                    </div>
                </div>

                <!-- Комментарий 3 -->
                <div class="comment-item d-flex align-items-start p-2 border rounded">
                    <div class="avatar-circle d-flex align-items-center justify-content-center bg-info text-white me-3">
                        АИ
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>Алексей Иванов</strong>
                                <div class="text-muted small">вчера</div>
                            </div>
                            <span class="badge bg-light text-dark small">Плагины</span>
                        </div>
                        <p class="mb-0 mt-1">Понравилась система плагинов, очень гибкая и удобная.</p>
                        <div class="comment-actions mt-2">
                            <a href="#" class="text-decoration-none me-3"><i class="bi bi-reply"></i> Ответить</a>
                            <a href="#" class="text-decoration-none"><i class="bi bi-trash"></i> Удалить</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-between">
                <button class="btn btn-sm btn-outline-primary refresh-comments" data-widget-id="recent_comments_widget">
                    <i class="bi bi-arrow-clockwise"></i> Обновить
                </button>
                <a href="/admin/comments" class="btn btn-sm btn-primary">
                    <i class="bi bi-list"></i> Все комментарии
                </a>
            </div>
        </div>
        <script>
            document.querySelector('.refresh-comments')?.addEventListener('click', function() {
                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                btn.disabled = true;

                fetch('/api/widgets/comments')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Здесь можно обновить список комментариев
                            console.log('Комментарии обновлены:', data);
                            showNotification('Список комментариев обновлен', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка обновления комментариев:', error);
                        showNotification('Ошибка обновления', 'error');
                    })
                    .finally(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    private function renderSystemMonitorWidget()
    {
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memoryLimit = ini_get('memory_limit');

        // Исправление: явное преобразование к float
        $freeBytes = disk_free_space("/");
        $totalBytes = disk_total_space("/");
        $freeGB = round((float)$freeBytes / 1024 / 1024 / 1024, 2);
        $totalGB = round((float)$totalBytes / 1024 / 1024 / 1024, 2);

        // Вычисление процента использования диска
        $usedPercent = 0;
        if ($totalBytes > 0) {
            $usedPercent = round((1 - ((float)$freeBytes / (float)$totalBytes)) * 100, 1);
        }

        ob_start();
        ?>
        <div class="system-monitor-widget">
            <div class="info-row d-flex justify-content-between align-items-center py-2 border-bottom">
                <span>PHP версия:</span>
                <strong><?= htmlspecialchars(phpversion()) ?></strong>
            </div>

            <div class="info-row d-flex justify-content-between align-items-center py-2 border-bottom">
                <span>Использование памяти:</span>
                <div>
                    <strong><?= $memoryUsage ?> MB</strong>
                    <small class="text-muted"> / <?= htmlspecialchars($memoryLimit) ?></small>
                </div>
            </div>

            <div class="info-row d-flex justify-content-between align-items-center py-2 border-bottom">
                <span>Загрузка CPU:</span>
                <div>
                    <span class="status-dot bg-success"></span>
                    <strong>Нормальная</strong>
                </div>
            </div>

            <div class="info-row d-flex justify-content-between align-items-center py-2 border-bottom">
                <span>Свободно на диске:</span>
                <strong><?= $freeGB ?> GB</strong>
            </div>

            <div class="info-row d-flex justify-content-between align-items-center py-2 border-bottom">
                <span>Время работы:</span>
                <strong><?= htmlspecialchars($this->getSystemUptime()) ?></strong>
            </div>

            <div class="info-row d-flex justify-content-between align-items-center py-2">
                <span>Последняя проверка:</span>
                <strong><?= date('H:i:s') ?></strong>
            </div>

            <div class="mt-3">
                <div class="progress mb-2" style="height: 10px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $usedPercent ?>%"
                         aria-valuenow="<?= $usedPercent ?>" aria-valuemin="0" aria-valuemax="100">
                        <span class="visually-hidden"><?= $usedPercent ?>%</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span>Загрузка системы</span>
                    <span><?= $usedPercent ?>%</span>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                    <button class="btn btn-sm btn-outline-info refresh-system" data-widget-id="system_monitor_widget">
                        <i class="bi bi-arrow-clockwise"></i> Обновить
                    </button>
                    <button class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-graph-up"></i> Детали
                    </button>
                </div>
            </div>
        </div>
        <script>
            document.querySelector('.refresh-system')?.addEventListener('click', function() {
                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                btn.disabled = true;

                fetch('/api/widgets/system')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Обновляем время последней проверки
                            const timeElement = btn.closest('.system-monitor-widget').querySelector('.info-row:last-child strong');
                            if (timeElement) {
                                const now = new Date();
                                timeElement.textContent = now.toLocaleTimeString();
                            }

                            // Обновляем использование памяти
                            const memoryElement = btn.closest('.system-monitor-widget').querySelector('.info-row:nth-child(2) strong');
                            if (memoryElement && data.data.memory) {
                                memoryElement.textContent = data.data.memory.usage;
                            }

                            // Обновляем прогресс бар
                            const progressBar = btn.closest('.system-monitor-widget').querySelector('.progress-bar');
                            const progressText = btn.closest('.system-monitor-widget').querySelector('.text-muted span:last-child');
                            if (progressBar && data.data.disk) {
                                const diskPercent = data.data.disk.used_percent;
                                progressBar.style.width = diskPercent + '%';
                                progressBar.setAttribute('aria-valuenow', diskPercent);
                                progressBar.textContent = diskPercent + '%';

                                if (progressText) {
                                    progressText.textContent = diskPercent + '%';
                                }
                            }

                            showNotification('Системная информация обновлена', 'success');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка обновления системной информации:', error);
                        showNotification('Ошибка обновления', 'error');
                    })
                    .finally(() => {
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}