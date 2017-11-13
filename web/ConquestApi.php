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

$app->post('', function(Request $request){
    global $container;
    
    $directory = dirname(__FILE__);
    $processManager = new \framework\process\ProcessManager();
    $pids = $processManager->GetRtmProcesses($directory);
    if (sizeof($pids) > 0)
    {
        error_log("RTM active, exiting");
        return new Response('', 200);
    }
    else
    {
        exec('/opt/php56/bin/php ' . $directory . '/rtmClient.php > /dev/null &');
    }
    
    $data = json_decode($request->getContent(), true);
    if ($data['type'] == 'url_verification')
    {
        return HandleVerification($data);
    }
    
    $strategy = $container->get('CommandStrategyFactory')->GetCommandStrategy($data['event']);
    
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

function HandleVerification($data)
{
    if ($data['token'] != Config::$BotToken)
    {
        return new Response('Invalid token', 400);
    }    
    return new Response($data['challenge'], 200);
}

$app->run();