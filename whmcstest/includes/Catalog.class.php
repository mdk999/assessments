<?php

class Catalog{

    public $isWeb = null;

    private $cmdNewLine = PHP_EOL;

    private $webNewLine = '<br />';

    public $newLine = null;

    private $catalogFolder = null;

    function __construct($searchDir){

        $this->isWeb = (php_sapi_name() == "cli") ? false : true; //checks to see if we are on the command line or not

        $this->newLine = ($this->isWeb) ? $this->webNewLine : $this->cmdNewLine; //sets the new line based on how we're called

        if(is_dir($searchDir)){


            $this->catalogFolder = $searchDir;

        }
        else die("Please enter catalog location{$this->newLine}");


    }

    
    public function searchCatalog($field, $term){

        $results = array();

        $dir = new RecursiveDirectoryIterator($this->catalogFolder,RecursiveDirectoryIterator::SKIP_DOTS);

        foreach (new RecursiveIteratorIterator($dir) as $file => $current) {

                    if($current->isDir()){
                        

                        $this->searchCatalog($field,$current->getPathname());

                    }
                    else {

                    $content = file_get_contents($current->getPathname());

                    if (stripos($content, $term) !== false) { //see if we even need to read this file

                        $catalogArr = $this->convertCatalog($content);

                        //isbn special case?

                        if(strcasecmp($field, 'isbn') == 0){


                            $isbnArr = explode('|',$catalogArr[$field]);

                            foreach($isbnArr as $val) if(strcasecmp($val, $term) == 0) $results[] = $catalogArr['isbn'];

                        }
                        elseif (is_array($catalogArr) && isset($catalogArr[$field]) && strcasecmp($catalogArr[$field], $term) == 0) $results[] = $catalogArr['isbn'];

                        }

                    }

                }

                    return $results;

            }


    public function convertCatalog($catalogStr){

            $catalog = array();

            $catalogArr = explode(PHP_EOL, $catalogStr);

                foreach($catalogArr as $fieldLines => $line){

                    parse_str($line,$lineArr);

                    if($lineArr) $catalog = array_merge($catalog,$lineArr);

                        }

            return $catalog;
    }

    public function displayResults($found){

        if(($found) && is_array($found)){

            foreach($found as $item) echo "{$item} {$this->newLine}";

        }
        else echo "nothing found {$this->newLine}";

        return;

    }


}
