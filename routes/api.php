<?php
/** @var \App\Core\Routing\Router $router */

// API routes
// RESTful API endpoints for the application
$router->group([
    'prefix' => '/api',
    'middleware' => 'api'
], function ($router) {
    // System status endpoint
    // Returns basic system status and current timestamp
    $router->get('/status', function() {
        return \App\Http\Response::json(['status' => 'ok', 'timestamp' => time()]);
    });
});
