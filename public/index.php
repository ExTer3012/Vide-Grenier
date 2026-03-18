<?php

/**
 * Front controller
 */

// Chargement de l'environnement en premier (variables d'env, constantes)
require dirname(__DIR__) . '/bootstrap/env.php';

// Composer autoload
require dirname(__DIR__) . '/vendor/autoload.php';

// Démarrage de la session sécurisée
session_set_cookie_params([
    'lifetime' => (int) \App\Config::SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => filter_var(\App\Config::SESSION_SECURE, FILTER_VALIDATE_BOOLEAN),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Gestion des erreurs selon l'environnement
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

// Routing
$router = new Core\Router();

$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'User', 'action' => 'login']);
$router->add('register', ['controller' => 'User', 'action' => 'register']);
$router->add('logout', ['controller' => 'User', 'action' => 'logout', 'private' => true]);
$router->add('account', ['controller' => 'User', 'action' => 'account', 'private' => true]);
$router->add('product', ['controller' => 'Product', 'action' => 'index', 'private' => true]);
$router->add('product/{id:\d+}', ['controller' => 'Product', 'action' => 'show']);
$router->add('api/{action}', ['controller' => 'Api']);
$router->add('{controller}/{action}');

try {
    $router->dispatch($_SERVER['QUERY_STRING']);
} catch (Exception $e) {
    switch ($e->getMessage()) {
        case 'You must be logged in':
            header('Location: /login');
            break;
        default:
            Core\Error::exceptionHandler($e);
    }
}