<?php

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');

$routes->get('/', 'Home::index');

// Admin 웹 페이지
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes) {

    $routes->get('/', 'AdminController::index');
    $routes->get('login', 'AdminController::loginForm');
    $routes->get('register', 'AdminController::registerForm');
    $routes->get('dashboard', 'DashboardController::index');
    
    // 사용자 관리
    $routes->get('users', 'UserController::index');
    $routes->get('users/create', 'UserController::create');
    
    // 대리점 관리 (슈퍼 관리자만)
    $routes->get('agency', 'AgencyController::index');
    $routes->get('agency/create', 'AgencyController::create');

    // 공지사항
    $routes->get('notices', 'NoticeController::index');
    $routes->get('notices/create', 'NoticeController::create');
    $routes->get('notices/(:num)/edit', 'NoticeController::edit/$1');

});

// API
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function($routes) {
    
    // 테스트
    $routes->match(['GET', 'POST'], 'hello', 'TestController::hello');

    // 관리자
    $routes->post('admin/register', 'AdminController::register');

    // 로그인
    $routes->post('admin/login', 'AdminController::login');
    $routes->post('admin/logout', 'AdminController::logout');
    $routes->post('users/login', 'UserController::login');
    
    // 대리점
    $routes->get('agency/(:num)', 'AgencyController::show/$1');
    $routes->post('agency', 'AgencyController::create');
    $routes->put('agency', 'AgencyController::modify');
    $routes->get('agency/dashboard/stats', 'AgencyController::getDashboardStats');
    $routes->get('agency/list', 'AgencyController::getList');

    // 사용자 관리
    $routes->get('users', 'UserController::index');
    $routes->post('users', 'UserController::create');
    $routes->get('users/(:num)', 'UserController::show/$1');
    $routes->put('users/(:num)', 'UserController::update/$1');
    $routes->delete('users/(:num)', 'UserController::delete/$1');
    $routes->post('users/(:num)/extend', 'UserController::extend/$1');
    $routes->put('users/(:num)/status', 'UserController::updateStatus/$1');
    $routes->get('users/(:num)/history', 'UserController::getHistory/$1');
    $routes->get('users/(:num)/logs', 'UserController::getLogs/$1');
    
    // 사용자 본인
    $routes->get('user/profile', 'UserController::getProfile');
    $routes->put('user/profile', 'UserController::updateProfile');

    // 공지사항
    $routes->get('notices', 'NoticeController::index');
    $routes->post('notices', 'NoticeController::create');
    $routes->get('notices/(:num)', 'NoticeController::show/$1');
    $routes->put('notices/(:num)', 'NoticeController::update/$1');
    $routes->delete('notices/(:num)', 'NoticeController::delete/$1');
    $routes->post('notices/(:num)/toggle-pin', 'NoticeController::togglePin/$1');

    // 로그
    $routes->post('logs', 'LogController::create');
    $routes->post('logs/batch', 'LogController::batch');
    $routes->get('logs/files', 'LogController::files'); // 관리자용
    $routes->get('logs/user/(:segment)', 'LogController::getUserLogs/$1');

});

