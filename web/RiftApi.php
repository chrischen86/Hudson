<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../AutoloadBootstrapper.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

$app->get('', function (Request $request)
{
    return new Response('', 200);
});

$app->post('/rift', function(Request $request){
    $data = json_decode($request->getContent(), true);
    error_log(print_r($data, 1));
    global $container;
    
    $processor = $container->get('RiftProcessor');    
    if ($processor != null)
    {
        $processor->Process($data);
        $processor->SendResponse();
    }

    return new Response('', 200);
});

$app->post('/slack/verify', function(Request $request){
    $data = json_decode($request->getContent(), true);
    if ($data['type'] != 'url_verification')
    {
        return new Response('Wrong type of request', 400);
    }
    if ($data['token'] != Config::$SlackToken)
    {
        return new Response('Invalid token', 400);
    }    
    return new Response($data['challenge'], 200);
});
$app->run();