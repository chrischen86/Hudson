<?php
namespace app;
use DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions(__DIR__ . '/' . \Config::$DiConfig);
$container = $containerBuilder->build();

return $container;