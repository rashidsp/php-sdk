<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->get('/', function (Request $request, Response $response, array $args){
    // Render index view
    return $response->withJson([
        'sdk_title'=> 'PHP SDK',
        'doc_link'=>'https://developers.optimizely.com/x/solutions/sdks/reference/index.html?language=php'
    ]);
});

$app->post('/config','DemoController:create');
$app->get('/config','DemoController:getConfig');
$app->get('/products','DemoController:listProducts');
$app->post('/visitor','DemoController:selectVisitor');
$app->post('/buy','DemoController:buy');
$app->get('/messages','DemoController:messages');
$app->DELETE('/messages','DemoController:clearMessages');