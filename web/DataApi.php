<?php

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../AutoloadBootstrapper.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/rift', function (Request $request) use ($app)
{
    $params = $request->query->all();
    foreach ($params as $key => $val)
    {
        error_log($key . ":" . $val);
    }
    return $app->json();
});

$app->post('/rift', function(Request $request){
    $data = $request->request->all();
    global $container;
    
    $processor = $container->get('RiftProcessor');    
    if ($processor != null)
    {
        $processor->Process($data);
        $processor->SendResponse();
    }

    return new Response('', 200);
});

$app->run();