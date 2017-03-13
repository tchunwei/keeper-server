<?php
require '../vendor/autoload.php';

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

$container = $app->getContainer();

// Register view on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__.'/../app/views', [
        'cache' => false
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    return $view;
};

// Register db on container
$container['db'] = function ($container) {
    $db = new medoo([
        'database_type' => 'pgsql',
        'database_name' => 'myfinance',
        'server' => '10.0.75.1',
        'username' => 'postgres',
        'password' => '$ViTrox$',
        'charset' => 'utf8'
    ]);

    return $db;
};

// Include routing table
require '../app/routes.php';

// Include all controllers
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('../app/controllers')) as $file) {
    if (strpos($file->getFilename(), ".php") !== false) {
        require_once $file;
        $filename = $file->getBasename(".php");
        // Register controller on container (pass view and db in)
        $container[$filename] = function ($container) use ($filename) {
            return new $filename($container['view'], $container['db']);
        };
    }
}

// Include all models
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('../app/models')) as $file) {
    if (strpos($file->getFilename(), ".php") !== false) {
        require_once $file;
        //$filename = $file->getBasename(".php");
        // Register model on container (pass db in)
        //$container[$filename] = function ($container) use ($filename) {
        //    return new $filename($container['db']);
        //};
    }
}

$app->run();