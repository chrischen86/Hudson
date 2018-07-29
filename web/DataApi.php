<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../AutoloadBootstrapper.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app["RiftController"] = function () use ($app)
{
    global $container;
    $dataService = $container->get('DataService');
    return new web\controllers\RiftController($dataService);
};
$app["RiftTypeController"] = function () use ($app)
{
    global $container;
    $repository = $container->get('RiftTypeRepository');
    return new \web\controllers\RiftTypeController($repository);
};
$app["UserController"] = function () use ($app)
{
    global $container;
    $repository = $container->get('UserRepository');
    return new \web\controllers\UserController($repository);
};

$app->get('/rift', 'RiftController:getRiftHistory');
$app->get('/riftType', 'RiftTypeController:get');
$app->get('/user', 'UserController:get');

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

$app->after(function (Request $request, Response $response)
{
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Headers', '*');
});

$app->options("{anything}", function ()
{
    return new JsonResponse(null, 204);
})->assert("anything", ".*");

$app->run();
