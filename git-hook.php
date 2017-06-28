<?php

echo "Begin: Pull code from GitHub<br/>";

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array

$array = preg_split('/\//', $input['ref']);
$branch = array_values(array_slice($array, -1))[0];

exec('git rev-parse --abbrev-ref HEAD', $output);
$currentBranch = $output[0];

exec('git fetch', $output);
if ($currentBranch != 'master')
{
    exec("git checkout $branch -f", $output);
}
exec('git pull', $output);
exec('/opt/php56/bin/php composer.phar install', $output);

PrintOutput($output);
echo "End: Pull code from Github<br/>";

function PrintOutput($output)
{
    foreach ($output as $o)
    {
        echo $o . '<br/>';
    }
}
