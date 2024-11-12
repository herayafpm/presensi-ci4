<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
$routes->group('auth',['namespace' => 'App\Controllers\Api'],function($routes){
    $routes->post('login','AuthApi::login');
    $routes->post('forgotpass','AuthApi::forgotpass');
    $routes->post('forgotpass_code','AuthApi::forgotpass_code');
    $routes->post('forgotpass_code_reset','AuthApi::forgotpass_code_reset');
    $routes->post('logout','AuthApi::logout',['namespace' => 'App\Controllers\Api','filter' => 'auth:api']);
});