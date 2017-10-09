<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../AutoloadBootstrapper.php';

if ($_GET['kill'] != 1)
{
    die('');
}

$processManager = new \framework\process\ProcessManager();
$pids = $processManager->GetRtmProcesses(dirname(__FILE__));

foreach ($pids as $pid)
{
    shell_exec('kill -9 ' . $pid);
}