<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\TestController;

$router->group(['middleware' => 'web'], function ($router) {
    // Основные маршруты
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/test', [TestController::class, 'index']);

    // Аутентификация
    $router->get('/login', [AuthController::class, 'login']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/logout', [AuthController::class, 'logout']);
    $router->get('/quick-login', [AuthController::class, 'quickLogin']);

    // Администраторские маршруты
    $router->get('/admin', [AdminController::class, 'dashboard']);
    $router->post('/admin/save-widgets', [AdminController::class, 'saveWidgets']);
    $router->post('/admin/toggle-widget', [AdminController::class, 'toggleWidget']);
    $router->get('/admin/hidden-widgets', [AdminController::class, 'getHiddenWidgets']);
    $router->get('/admin/widget-info/{id}', [AdminController::class, 'getWidgetInfo']);
    $router->get('/admin/widget-html/{id}', [AdminController::class, 'getWidgetHtml']);

    // Плагины
    $router->get('/admin/plugins', [PluginController::class, 'index']);
    $router->post('/admin/plugins/activate/{pluginName}', [PluginController::class, 'activate']);
    $router->post('/admin/plugins/deactivate/{pluginName}', [PluginController::class, 'deactivate']);
});