<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/framework/process/ProcessManager.php';
require_once __DIR__ . '/../framework/slack/ISlackApi.php';
require_once __DIR__ . '/../framework/slack/SlackApi.php';
require_once __DIR__ . '/../Config.php';

use framework\slack\SlackApi;

error_log("Begin: Pull code from GitHub");

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array

$array = preg_split('/\//', $input['ref']);
$branch = array_values(array_slice($array, -1))[0];

//exec('git rev-parse --abbrev-ref HEAD', $output);
//$currentBranch = $output[0];
exec('pwd', $output);
$currentDirectory = $output[0];

exec('git fetch', $output);
if (preg_match('/(Friday)/i', $currentDirectory))
{
    exec("git checkout $branch -f", $output);
}
exec('git pull', $output);
exec('/opt/php56/bin/php composer.phar install', $output);

PrintOutput($output);
error_log("End: Pull code from Github");

error_log("Restarting RTM Client");
sendUpdate($input);
killProcessess($currentDirectory);
exec('/opt/php56/bin/php ' . dirname(__FILE__) . '/web/rtmClient.php > /dev/null &');

error_log("Completed restart process");
function PrintOutput($output)
{
    foreach ($output as $o)
    {
        error_log($o);
    }
}

function killProcessess($currentDirectory)
{
    $processManager = new \framework\process\ProcessManager();
    $pids = $processManager->GetRtmProcesses($currentDirectory . '/web');

    foreach ($pids as $pid)
    {
        shell_exec('kill -9 ' . $pid);
    }
}

function sendUpdate($json)
{
    $headCommit = $json['head_commit'];
    $api = new SlackApi();

    $attachment = array();
    array_push($attachment, array(
        'text' => $headCommit['message'],
        'title' => 'Commit Reference',
        'title_link' => $headCommit['url'],
        'footer' => 'GitHub',
        'footer_icon' => 'http://projectr.ca/images/seo-web-code-icon.png',
        'ts' => time()
    ));

    $api->SendMessage("I am being taken offline for an update!", $attachment, "test2");
}
