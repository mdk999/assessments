<?php

require_once("./includes/Catalog.class.php");

$dir = './catalog'; //path to search

$catalog = new Catalog($dir); //instantiate Catalog class

$field = $term = null;

if($catalog->isWeb){

    if(isset($_SERVER['QUERY_STRING']))parse_str($_SERVER['QUERY_STRING'], $vars); //we should have passed-in variable

}
else {


   if(isset($argv)) parse_str($argv[1], $vars); //get command line passed variable

}

    if(isset($vars) && is_array($vars)){

    reset($vars); //for good measure

    $field = filter_var(key($vars),FILTER_SANITIZE_STRING);

    $term = filter_var(current($vars),FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES);

    if($results = $catalog->searchCatalog($field,$term)) 

    $catalog->displayResults($results);

    else die("No results found{$catalog->newLine}");

    }
    else die("Please enter search field and value, ie. php search.php author=\"Paul Hudson\" or via the bowser - search.php?author=Paul Hudson {$catalog->newLine}");



