<?php

    declare(strict_types=1);

    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\AdminController;
    use App\Http\Controllers\PluginController;
    use App\Http\Controllers\TestController;
    use App\Http\Response;

    /** @var \App\Core\Routing\Router $router */

    // Базовые маршруты
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/about', function() {
        echo "Страница о системе (в разработке)";
    });
    $router->get('/docs', function() {
        echo "Документация (в разработке)";
    });

    // Аутентификация
    $router->get('/login', [AuthController::class, 'login']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/logout', [AuthController::class, 'logout']);
    $router->get('/quick-login', [AuthController::class, 'quickLogin']);

    // Административные маршруты
    $router->get('/admin', [AdminController::class, 'dashboard']);

    // Управление плагинами
    $router->get('/admin/plugins', [PluginController::class, 'index']);
    $router->post('/admin/plugins/activate/{pluginName}', [PluginController::class, 'activate']);
    $router->post('/admin/plugins/deactivate/{pluginName}', [PluginController::class, 'deactivate']);

    // Управление виджетами
    $router->post('/admin/save-widgets', [AdminController::class, 'saveWidgets']);
    $router->post('/admin/toggle-widget', [AdminController::class, 'toggleWidget']);
    $router->get('/admin/get-hidden-widgets', [AdminController::class, 'getHiddenWidgets']);
    $router->get('/admin/widget-info/{widgetId}', [AdminController::class, 'getWidgetInfo']);
    $router->get('/admin/widget-html/{widgetId}', [AdminController::class, 'getWidgetHtml']);

    // Тестовый маршрут
    $router->get('/test', [TestController::class, 'index']);