<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// API 라우트 그룹
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->match(['get', 'post'], 'hello', 'TestController::hello');
});