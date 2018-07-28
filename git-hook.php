<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/framework/process/ProcessManager.php';
require_once __DIR__ . '/framework/slack/ISlackApi.php';
require_once __DIR__ . '/framework/slack/SlackApiBase.php';
require_once __DIR__ . '/framework/slack/SlackApi.php';

use framework\slack\SlackApi;

error_log("Begin: Pull code from GitHub");

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array
//Return resposne right away
ignore_user_abort(true);
set_time_limit(0);

ob_start();
// do initial processing here
echo $response; // send the response
header('Connection: close');
header('Content-Length: ' . ob_get_length());
ob_end_flush();
ob_flush();
flush();
//

$array = preg_split('/\//', $input['ref']);
$branch = array_values(array_slice($array, -1))[0];

//exec('git rev-parse --abbrev-ref HEAD', $output);
//$currentBranch = $output[0];
exec('pwd', $output);
$currentDirectory = $output[0];

//Do not update jarvis if changes aren't for master and conversly
//do not update friday if changes are for master
if ((preg_match('/(Hudson)/i', $currentDirectory) && $branch != 'master') || (preg_match('/(Friday)/i', $currentDirectory) && $branch == 'master'))
{
    return;
}

exec('git fetch', $output);
if (preg_match('/(Friday)/i', $currentDirectory))
{
    exec("git checkout $branch -f", $output);
}
exec('git pull', $output);
exec('/opt/php56/bin/php composer.phar update', $output);

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
    if ($headCommit == null)
    {
        return;
    }

    $api = new SlackApi();

    $attachment = array();
    array_push($attachment, array(
        'text' => $headCommit['message'],
        'title' => 'Commit Reference',
        //'title_link' => $headCommit['url'],
        'footer' => 'GitHub',
        'footer_icon' => 'http://projectr.ca/images/seo-web-code-icon.png',
        'ts' => time()
    ));
    if (Config::$UpdateVerbosity > 0)
    {
        $api->SendMessage("I am being taken offline for an update!  Systems will be back online shortly.", $attachment, Config::$UpdateChannel);
    }
    else
    {
      $api->SendEphemeral("I am being taken offline for an update!  Systems will be back online shortly.", Config::$Admin, Config::$UpdateChannel, $attachment);  
    }
}
