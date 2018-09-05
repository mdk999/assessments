<?php
session_start();

//error_reporting(E_ALL);

//ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

$cfg = require_once __DIR__ . '/config.php';

use App\Api\TvDb;

$tvdb = new TvDb($cfg);

$action =  filter_var($_GET['action'], FILTER_SANITIZE_STRING) ?? null;

switch ($action) {
    case 'getEpisodes':
        $seriesId = $_GET['series_id'] ?? null;
        if ($seriesId) {
            $output = $tvdb->getEpisodes($seriesId);
        } else {
            $output = json_encode([
                'Error' => 'No series id specified'
            ]);
        }     
        break;

    case 'search':
        $term = $_GET['term'] ?? null;
        if ($term) { 
            $output = $tvdb->searchSeries($term);
        }
        break;
    case 'refresh':
        $tvdb->jwtRefresh();
        exit;
        break;
    default:
        $output = json_encode([
            'Error' => 'No action specified'
        ]);
        
}

header("Content-Type: application/json; charset=utf-8", true);

echo $output;

session_write_close();

