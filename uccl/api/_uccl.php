<?php

session_start();

error_reporting(E_ALL);

ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

$cfg = require_once __DIR__ . '/config.php';

use Classes\UcCl;

$uccl = new UcCl($cfg);

$get_action =  $_GET['action'] ?? null;

$action =  filter_var($get_action, FILTER_SANITIZE_STRING) ?? null;

$output = json_encode([
    'Error' => 'No action specified'
]);


switch ($action) {
    case 'i': //init and insert listings
        $uccl->updateListings();
    break;

    case 'g': //get listings
        $output = json_encode($uccl->getListings());
    break;

    default:
    //override $output if needed

}



header("Content-Type: application/json; charset=utf-8", true);

echo $output;

session_write_close();
