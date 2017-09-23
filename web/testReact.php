<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../AutoloadBootstrapper.php';

$loop = React\EventLoop\Factory::create();

$client = new Slack\RealTimeClient($loop);
$client->setToken(Config::$BotUserOAuthToken);

// disconnect after first message
$client->on('message', function ($data) use ($client) {
    error_log(print_r($data, true));
    
    global $container;
    $container->get('CommandStrategyFactory');
    $strategy = $container->get('CommandStrategyFactory')->GetCommandStrategy($request);
    
    if ($strategy != null)
    {
        $strategy->Process($data['event']);
        $strategy->SendResponse();
    }
});

$client->connect()->then(function () {
    echo "Connected!\n";
});

$loop->run();