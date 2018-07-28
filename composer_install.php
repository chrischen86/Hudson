<?php
require 'vendor/autoload.php'; // require composer dependencies

use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

// Composer\Factory::getHomeDir() method 
// needs COMPOSER_HOME environment variable set
putenv('COMPOSER_HOME=' . __DIR__ . '/vendor/bin/composer');

// call `composer install` command programmatically
$input = new ArrayInput(array('command' => 'install'));
$application = new Application();
$application->setAutoExit(false); // prevent `$application->run` method from exitting the script
$application->run($input);

echo "Done.";