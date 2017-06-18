<?php

echo "Begin: Pull code from GitHub<br/>";

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array

$array = preg_split('/\//', $input['ref']);
$branch = array_values(array_slice($array, -1))[0];

exec('git fetch', $output);
PrintOutput($output);

if ($branch != 'master')
{
    exec("git checkout $branch -f", $output);
    PrintOutput($output);
}

exec('git pull', $output);
PrintOutput($output);

echo "End: Pull code from Github<br/>";

function PrintOutput($output)
{
    foreach ($output as $o)
    {
        echo $o . '<br/>';
    }
}
