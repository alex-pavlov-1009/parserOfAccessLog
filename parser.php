<?php

include './src/Parser.php';


use Parser\Parser;

$file_name = './access_log';

if(array_key_exists(1, $argv) && !empty($argv[1])){
    $file_name = $argv[1];
}

$parser = new Parser($file_name);

$result = json_encode($parser->parsLog());

echo "$result \n";