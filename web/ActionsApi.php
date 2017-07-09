<?php

require_once __DIR__.'/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

$app->post('', function(Request $request){
    $data = json_decode($request->getContent(), true);
    error_log(print_r($data, 1));
    return new Response('', 200);
});


$app->run();