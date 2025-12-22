<?php

    declare(strict_types=1);

    use App\Http\Response;
    use App\Core\Widgets\WidgetManager;

    /** @var \App\Core\Routing\Router $router */

    // Здесь будут API маршруты
    $router->get('/status', function() {
        return Response::json(['status' => 'ok', 'timestamp' => time()]);
    });

    $router->get('/widgets', function() {
        $widgetManager = WidgetManager::getInstance();
        $widgets = $widgetManager->getWidgets();
        return Response::json($widgets);
    });