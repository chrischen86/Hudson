<?php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../AutoloadBootstrapper.php';

$loop = React\EventLoop\Factory::create();

$client = new Slack\RealTimeClient($loop);
$client->setToken(Config::$BotToken);

// disconnect after first message
$client->on('message', function ($data) use ($client) {
    echo "Someone typed a message: ".$data['text']."\n";
    $client->disconnect();
});

$client->connect()->then(function () {
    echo "Connected!\n";
});

$loop->run();