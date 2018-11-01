<?php


$skey = 'DEFGHIJKMLNOPQRSTUVWXYABZC';

$skeyArr = str_split($skey);

$arr = array('A','J','R','U','Z','A','H','F','U','E','W','H','J','A','B','C','C','C','X','Y','A','G','T','R','U','V','X');

function custSort($a,$b){

global $skeyArr;

$tmp = array();

$aI = array_search ($a, $skeyArr) ?? -1;

$bI = array_search ($b, $skeyArr) ?? -1;

if ($aI == $bI) {
        return 0;
    }

return ($aI < $bI) ? -1 : 1;
}

//print_r($skeyArr);

//print_r($arr);


usort($arr,'custSort');

print_r($arr);
