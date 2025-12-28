<?php
/** @var \App\Core\Routing\Router $router */

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\TestController;

// Web routes with web middleware
$router->group(['middleware' => 'web'], function ($router) {
    // Home and test routes
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/test', [TestController::class, 'index']);

    // Authentication routes
    $router->get('/login', [AuthController::class, 'login']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->get('/logout', [AuthController::class, 'logout']);
    $router->get('/quick-login', [AuthController::class, 'quickLogin']);

    // Admin dashboard routes
    $router->get('/admin', [AdminController::class, 'dashboard']);
    $router->post('/admin/save-widgets', [AdminController::class, 'saveWidgets']);
    $router->post('/admin/toggle-widget', [AdminController::class, 'toggleWidget']);
    $router->get('/admin/hidden-widgets', [AdminController::class, 'getHiddenWidgets']); // JSON API
    $router->get('/admin/hidden-widgets-page', [AdminController::class, 'getHiddenWidgetsPage']); // HTML страница
    $router->get('/admin/widget-info/{id}', [AdminController::class, 'getWidgetInfo']);
    $router->get('/admin/widget-html/{id}', [AdminController::class, 'getWidgetHtml']);

    // Plugin management routes
    $router->get('/admin/plugins', [PluginController::class, 'index']);
    $router->post('/admin/plugins/activate/{pluginName}', [PluginController::class, 'activate']);
    $router->post('/admin/plugins/deactivate/{pluginName}', [PluginController::class, 'deactivate']);
});