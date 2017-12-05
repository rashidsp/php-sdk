<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// Register globally to app
$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
//    $this->session = new \SlimSession\Helper;
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
};


$container['DemoController'] = function($container){
    return new \App\controllers\DemoController($container['logger']);
};