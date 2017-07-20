<?php
/**
 * File "index.php"
 * @author Thomas Bourrely
 * 20/07/2017
 */

require_once __DIR__ . '/vendor/autoload.php';

session_start();

$app = new Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);


// Get container
$container = $app->getContainer();

// Register twig-view on container
$container['views'] = function( $container ) {
    $view = new \Slim\Views\Twig('src/views', [
        'cache' => false // disable cache
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};


/******************
 * START OF ROUTES
 *****************/

$app->get( '/', \clientApp\controllers\HomeController::class . ':home' )->setName('home');

// Start the process to get an authorization code from the authorization server
$app->get( '/login', \clientApp\controllers\ApiController::class . ':login' )->setName('API_login.get');

// Receive the authorization code => ask for an access token
$app->get( '/code', \clientApp\controllers\ApiController::class . ':code' )->setName('API_code.get');

// remove access token from the session
$app->get( '/logout', \clientApp\controllers\ApiController::class . ':logout' )->setName('API_logout.get');

/******************
 * END OF ROUTES
 *****************/


// start slim
$app->run();