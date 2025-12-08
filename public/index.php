<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use App\Config\Router;

// Initialize router
$router = new Router();

// Define routes - basic GET/POST for home page
$router->addRoute('GET', '/', 'HomeController@index');

// API routes - basic GET/POST examples
$router->addRoute('GET', '/api/example', 'ApiController@getExample');
$router->addRoute('POST', '/api/example', 'ApiController@postExample');

// Handle the request
$router->handleRequest(); 