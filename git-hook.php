<?php
echo "Begin: Pull code from GitHub<br/>";

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array

error_log(print_r($input, 1));
exec('git pull', $output);
foreach ($output as $o) {
    echo $o . '<br/>';
}
echo "End: Pull code from Github<br/>";