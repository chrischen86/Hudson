<?php

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

exec('ps ahxwwo pid,command', $out);
$pid = getPid($currentDirectory, $out);
shell_exec('kill -9 ' . $pid);

exec('/opt/php56/bin/php ' . dirname(__FILE__) . '/web/rtmClient.php > /dev/null &');

error_log("Completed restart process");
function PrintOutput($output)
{
    foreach ($output as $o)
    {
        error_log($o);
    }
}

function getPid($currentDirectory, $out)
{
    foreach ($out as $item)
    {
        if (strpos($item, $currentDirectory . '/web/rtmClient.php') === false)
        {
            continue;
        }
        
        $matches = [];
        $re = '/(?:\s)(\d+)/';
        if (preg_match($re, $item, $matches))
        {
            return $matches[1];
        }		
    }
    return null;
}