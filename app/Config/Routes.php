<?php

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');

$routes->get('/', 'Home::index');

// API
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function($routes) {
    
    // 테스트
    $routes->match(['get', 'post'], 'hello', 'TestController::hello');

    // 로그인
    $routes->post('admin/login', 'AdminController::login');
    $routes->post('admin/logout', 'AdminController::logout');
    $routes->post('users/login', 'UserController::login');
    
    // 대리점
    $routes->get('agency/(:num)', 'AgencyController::show/$1');
    $routes->post('agency', 'AgencyController::create');
    $routes->put('agency', 'AgencyController::modify');
    $routes->get('agency/dashboard/stats', 'AgencyController::getDashboardStats');

    // 사용자 관리
    $routes->get('users', 'UserController::index');
    $routes->post('users', 'UserController::create');
    $routes->get('users/(:num)', 'UserController::show/$1');
    $routes->put('users/(:num)', 'UserController::update/$1');
    $routes->delete('users/(:num)', 'UserController::delete/$1');
    $routes->post('users/(:num)/extend', 'UserController::extend/$1');
    $routes->put('users/(:num)/status', 'UserController::updateStatus/$1');
    $routes->get('users/(:num)/history', 'UserController::getHistory/$1');
    
    // 사용자 본인
    $routes->get('user/profile', 'UserController::getProfile');
    $routes->put('user/profile', 'UserController::updateProfile');
});