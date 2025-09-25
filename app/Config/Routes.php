<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

$routes->get('/', 'Home::index');

// 에러 페이지
$routes->get('error/404', 'ErrorController::show404');
$routes->get('error/500', 'ErrorController::show500');

// API
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function($routes) {
    // 테스트
    $routes->match(['get', 'post'], 'hello', 'TestController::hello');

    // 인증 관련
    $routes->group('auth', function($routes) {
        $routes->post('login', 'AuthController::login');
        $routes->post('logout', 'AuthController::logout', ['filter' => 'api_auth']);
    });
    
    // 대리점
    $routes->group('agency', ['filter' => 'api_auth'], function($routes) {
        $routes->get('(:num)', 'AgencyController::show/$1');
        $routes->post('/', 'AgencyController::create');
        $routes->put('/', 'AgencyController::update');
        
        // 대시보드 통계
        $routes->get('dashboard/stats', 'AgencyController::getDashboardStats');
        
    });

    // 사용자
    $routes->group('users', function($routes) {
        $routes->get('/', 'UserController::index'); // 목록 조회 (페이징, 검색)
        $routes->post('/', 'UserController::create'); // 신규 등록
        $routes->get('(:num)', 'UserController::show/$1'); // 상세 조회
        $routes->put('(:num)', 'UserController::update/$1'); // 정보 수정
        $routes->delete('(:num)', 'UserController::delete/$1'); // 삭제

        $routes->post('(:num)/extend', 'UserController::extend/$1'); // 기간 연장
        $routes->put('(:num)/status', 'UserController::updateStatus/$1'); // 상태 변경
        $routes->get('(:num)/history', 'UserController::getHistory/$1'); // 이력 조회
    });
});