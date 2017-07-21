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

$app->get('/activezones', function (Request $request) use ($app)
{
    global $container;
    $zoneManager = $container->get('ZoneManager');
    $result = $zoneManager->GetStrikeTable();
    
    return $app->json($result);
});

$app->post('/attack', function (Request $request) use ($app)
{
    global $container;
    $zoneManager = $container->get('ZoneManager');
    $zone = $request->get("zone");
    $node = $request->get("node");
    $user = $request->get("user");
    $result = $zoneManager->ClaimNode($zone, $node, $user);
    
    return $app->json($result);
});

$app->post('', function(Request $request){
    global $container;
    $data = json_decode($request->getContent(), true);
    if ($data['type'] == 'url_verification')
    {
        return HandleVerification($data);
    }
    
    $strategy = $container->get('CommandStrategyFactory')->GetCommandStrategy($request);    
    if ($strategy != null)
    {
        $strategy->Process($data['event']);
        $strategy->SendResponse();
    }
    return new Response('', 200);
});

$app->post('/slack/verify', function(Request $request){
    $data = json_decode($request->getContent(), true);
    if ($data['type'] != 'url_verification')
    {
        return new Response('Wrong type of request', 400);
    }
    if ($data['token'] != Config::$BotToken)
    {
        return new Response('Invalid token', 400);
    }    
    return new Response($data['challenge'], 200);
});

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->error(function(Exception $e, Request $request, $code) use ($app)
{
    $response = array("error" => true, "message" => $e->getMessage());
    return $app->json($response);
});

function HandleVerification($data)
{
    if ($data['token'] != Config::$BotToken)
    {
        return new Response('Invalid token', 400);
    }    
    return new Response($data['challenge'], 200);
}

$app->run();