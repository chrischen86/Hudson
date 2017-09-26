<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../AutoloadBootstrapper.php';

$loop = React\EventLoop\Factory::create();

$client = new Slack\RealTimeClient($loop);
$client->setToken(Config::$BotUserOAuthToken);

// disconnect after first message
$client->on('message', function ($data) use ($client) {
    global $container;
    $container->get('CommandStrategyFactory');
    $strategy = $container->get('CommandStrategyFactory')->GetCommandStrategy($data);
    
    if ($strategy != null)
    {
        $strategy->Process($data);
        $strategy->SendResponse();
    }
});

$client->connect()->then(function () {
    error_log("Connected!\n");
    global $container;
    $api = $container->get('ISlackApi');
    $api->SendMessage("Systems back online!", null, Config::$UpdateChannel);
});

$loop->run();