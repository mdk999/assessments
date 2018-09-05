<?php

global $num_path;

$num_path = 0;

//paths to check
$adj = array();

$adj['A']['B'] = 1;
$adj['A']['C'] = 1;
$adj['A']['D'] = 1;
$adj['B']['A'] = 1;
$adj['B']['C'] = 1;
$adj['B']['E'] = 1;
$adj['C']['A'] = 1;
$adj['C']['B'] = 1;
$adj['C']['D'] = 1;
$adj['C']['E'] = 1;
$adj['D']['A'] = 1;
$adj['D']['C'] = 1;
$adj['D']['E'] = 1;
$adj['D']['F'] = 1;
$adj['E']['B'] = 1;
$adj['E']['C'] = 1;
$adj['E']['D'] = 1;
$adj['E']['F'] = 1;
$adj['F']['D'] = 1;
$adj['F']['E'] = 1;

function print_path($path) {

    //number of paths found
    global $num_path;
    
	printf("path #%d = %s\n", $num_path, implode('->', $path));
}

//Depth First Search Algo
function dfs($a, $adj, $path, $len) {

    global $num_path;
    
    //10 'edges' to account for, if we have all then solution is complete
	if ($len >= 10) {

        //number of possible solutions 6^11, we're not going to even try that many
        ++$num_path;
        
        print_path($path);
        
	} else {

        //Paths are visted only once
		for ($b = 'A' ; $b <= 'F' ; ++$b) {

			if ($adj[$a][$b]) {

                $new_path = $path;
                
                $new_adj = $adj;
                
			// add to path
                array_push($new_path, $b);
                
			// remove edge from adjacency matrix
                $new_adj[$a][$b] = 0;
                
                $new_adj[$b][$a] = 0;
                
				dfs($b, $new_adj, $new_path, $len + 1);
			}
		}
	}
}

//set pairs to 0

for ($a = 'A' ; $a <= 'F' ; ++$a) {

	for ($b = 'A' ; $b <= 'F' ; ++$b) {

		$adj[$a][$b] = 0;
	}
}


// Note: all solutions start at A or B,
// and all solutions starting at B
// are simply a reflection of a solution starting at A
// Therefore, we only consider solutions starting from A

dfs('A', $adj, array('A'), 0);


