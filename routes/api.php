<?php

$router->group([
    'prefix' => '/api',
    'middleware' => 'api'
], function ($router) {
    $router->get('/status', function() {
        return \App\Http\Response::json(['status' => 'ok', 'timestamp' => time()]);
    });
});