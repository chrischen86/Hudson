<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../AutoloadBootstrapper.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use web\controllers\RiftController;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app["RiftController"] = function () use ($app)
{
    global $container;
    $repository = $container->get('RiftHistoryRepository');
    return new RiftController($repository);
};

$app->get('/rift', 'RiftController:getRiftHistory');

$app->post('/rift', function(Request $request)
{
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
