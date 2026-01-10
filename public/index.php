<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use App\Config\Router;

// Initialize router
$router = new Router();

// Define routes
$router->addRoute('GET', '/', 'HomeController@index');
$router->addRoute('GET', '/login', 'AuthController@loginForm');
$router->addRoute('POST', '/login', 'AuthController@login');
$router->addRoute('GET', '/register', 'AuthController@registerForm');
$router->addRoute('POST', '/register', 'AuthController@register');
$router->addRoute('GET', '/logout', 'AuthController@logout');

// Generate routes
$router->addRoute('GET', '/generate', 'GenerateController@index');
$router->addRoute('POST', '/generate', 'GenerateController@index');

// Study Cards routes
$router->addRoute('GET', '/study-cards', 'StudyCardController@index');
$router->addRoute('GET', '/study-cards/load/{id}', 'StudyCardController@loadByFolder');
$router->addRoute('POST', '/study-cards/generate', 'StudyCardController@generate');
$router->addRoute('POST', '/study-cards/delete-all', 'StudyCardController@deleteAllByFolder');
$router->addRoute('POST', '/study-cards/create', 'StudyCardController@create');
$router->addRoute('POST', '/study-cards/delete', 'StudyCardController@delete');

// Handle the request
$router->handleRequest(); 