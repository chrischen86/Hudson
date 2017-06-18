<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../AutoloadBootstrapper.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use framework\CommandProcessorFactory;

$app = new Silex\Application();
$app['debug'] = true;

$app->get('', function (Request $request)
{
    $conquestManager = new \framework\conquest\ConquestManager();
    $conquestManager->GetLastPhaseStats();
    echo var_dump($conquestManager);
    return new Response('', 200);
});

$app->post('', function(Request $request){
    $data = json_decode($request->getContent(), true);
    error_log(print_r($data, 1));
    if ($data['type'] == 'url_verification')
    {
        return HandleVerification($data);
    }    
    $commandProcessor = new CommandProcessorFactory();
    $processor = $commandProcessor->CreateProcessor($request);
    if ($processor != null)
    {
        $processor->Process();
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
    if ($data['token'] != Config::$BotToken)
    {
        return new Response('Invalid token', 400);
    }    
    return new Response($data['challenge'], 200);
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