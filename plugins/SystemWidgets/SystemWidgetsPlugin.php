<?php
declare(strict_types=1);

namespace Plugins\SystemWidgets;

use App\Core\Widgets\WidgetManager;
use App\Core\HookManager;

class SystemWidgetsPlugin
{
    private $widgetManager;
    private $hookManager;

    public function init()
    {
        $this->widgetManager = WidgetManager::getInstance();
        $this->hookManager = HookManager::getInstance();

        $widgets = $this->getSystemWidgets();
        $widgetIds = array_column($widgets, 'id');

        // Регистрируем виджеты
        $this->registerSystemWidgets();

        // Инициализируем их как активные по умолчанию
        \App\Core\Widgets\WidgetInitializer::initializeDefaultWidgets(
            $this->widgetManager,
            $widgetIds
        );

        $this->registerHooks();
    }

    private function registerSystemWidgets()
    {
        $widgets = $this->getSystemWidgets();

        foreach ($widgets as $widgetData) {
            $this->widgetManager->registerWidget($widgetData);
        }
    }

    private function getSystemWidgets(): array
    {
        return [
            [
                'id' => 'system_stats',
                'title' => 'Статистика системы',
                'description' => 'Основные показатели системы',
                'icon' => 'bi-speedometer2',
                'size' => 'medium',
                'source' => 'system',
                'content' => function() {
                    return '
                    <div class="row">
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="bi bi-plug"></i>
                                </div>
                                <div class="stat-info">
                                    <h5>0</h5>
                                    <small>Плагинов</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="stat-info">
                                    <h5>1</h5>
                                    <small>Пользователей</small>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            ],
            [
                'id' => 'recent_activity',
                'title' => 'Последние действия',
                'description' => 'История активности',
                'icon' => 'bi-clock-history',
                'size' => 'medium',
                'source' => 'system',
                'content' => function() {
                    return '
                    <ul class="activity-list">
                        <li>
                            <i class="bi bi-check-circle text-success"></i>
                            <span>Вы вошли в систему</span>
                            <small>Только что</small>
                        </li>
                    </ul>';
                }
            ],
            [
                'id' => 'quick_links',
                'title' => 'Быстрые ссылки',
                'description' => 'Доступ к основным разделам',
                'icon' => 'bi-link-45deg',
                'size' => 'small',
                'source' => 'system',
                'content' => function() {
                    return '
                    <div class="quick-links">
                        <a href="/admin/plugins" class="quick-link">
                            <i class="bi bi-plug"></i>
                            <span>Плагины</span>
                        </a>
                        <a href="#" class="quick-link">
                            <i class="bi bi-palette"></i>
                            <span>Темы</span>
                        </a>
                        <a href="#" class="quick-link">
                            <i class="bi bi-gear"></i>
                            <span>Настройки</span>
                        </a>
                    </div>';
                }
            ],
            [
                'id' => 'server_info',
                'title' => 'Информация о сервере',
                'description' => 'Технические данные',
                'icon' => 'bi-server',
                'size' => 'large',
                'source' => 'system',
                'content' => function() {
                    return '
                    <div class="server-info">
                        <div class="info-row">
                            <span>PHP версия:</span>
                            <strong>' . phpversion() . '</strong>
                        </div>
                        <div class="info-row">
                            <span>Версия MySQL:</span>
                            <strong>5.7+</strong>
                        </div>
                        <div class="info-row">
                            <span>Память:</span>
                            <strong>' . ini_get('memory_limit') . '</strong>
                        </div>
                    </div>';
                }
            ]
        ];
    }

    private function registerHooks()
    {
        // Хуки для расширения функциональности
        $this->hookManager->addAction('widget_before_render', function($widgetId) {
            // Можно добавить логику перед рендерингом виджета
        });

        $this->hookManager->addFilter('widget_content', function($content, $widgetId) {
            // Можно модифицировать контент виджета
            return $content;
        }, 10, 2);
    }

    public function registerRoutes($router)
    {
        // Маршруты для системного плагина (если понадобятся)
        $router->get('/system-widgets/test', function() {
            echo "Test route for SystemWidgets plugin";
        });
    }
}