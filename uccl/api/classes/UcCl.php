<?php

namespace Classes;

use DOMDocument;

class UcCl 
{

    public $errorMsg;

    //holds the app config
    private $config;

    private $db;

    private $now;

    private $nowTS;

    private $lastModifiedTS;

    private $metrics = array();

    public function __construct($conf){

        if(!is_array($conf)){

            throw new \Exception('config options not found');

        }
        else {
            
            $this->config = $conf;

            $this->db = $this->getDB();
        
            $this->now = date("Y-m-d H:i:s");

            $this->nowTS = time();

            $this->lastModifiedTS = strtotime($this->getLastModifiedDate());

        }

        return;


    }


    //gets a database instance
    function getDB(){

        if(!isset($this->db)){

            if($dblink = new \PDO("mysql:host={$this->config['database']['host']};dbname={$this->config['database']['name']}", $this->config['database']['user'], $this->config['database']['pass'])){

                // to enable warning as errors.
               $dblink->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING );
 
               return $dblink;
            }
            else throw new \Exception('unable to connect to db');

        }
        else return $this->db;

    }

    function parseUrlContent($content){


        if(is_string($content)){

            $src = new \DOMDocument();

            //we could clean up the incoming html with say tidy but for now we'll suppress warnings
            /*
            $tidy_config = array( 
                     'clean' => true, 
                     'output-xhtml' => true, 
                     'show-body-only' => true, 
                     'wrap' => 0, 
                     ); 

            $tidy = tidy_parse_string( $html, $tidy_config, 'UTF8'); 
            $tidy->cleanRepair(); 
            */
            @$src->loadHTML($content);

            //$results = $src->getElementById('sortbale-results');

            $xpath = new \DOMXpath($src);

            $results = $xpath->query("//li[@class='result-row']");

            if($results->length > 0){ //did we get results?

            $listings = array();

            foreach($results as $result){


                $br = $location = $picture = '';

                $info =  $result->getElementsByTagName("p");

                if($info->length > 0){ //did we find the listing info?

                $xtitle = $xpath->query(".//a[contains(@class,'result-title')]",$result);

                if($xhousing = $xpath->query(".//span[@class='housing']",$result)){

                    if(strpos($xhousing[0]->nodeValue,'br') !== false ){
                
                        $brArr = explode('-',$xhousing[0]->nodeValue);

                        $br = $brArr[0];

                    }
                
                }

                $xcost = $xpath->query(".//span[@class='result-price']",$result);

                if($xlocation = $xpath->query(".//span[@class='result-hood']",$result)){

                    $location = $xlocation[0]->nodeValue ?? null;
                }

                $xpicture = $xpath->query(".//a[contains(@class,'result-image')]",$result);

                $imgs = explode(',',$xpicture[0]->getAttribute('data-ids')); //this was interesting. CL uses the data-ids attribute to build the image gallery on demand

                if($xthumb = substr($imgs[0],2) ?? null){
                
                $picture = ($xthumb) ? "{$this->config['cl_img_url']}/{$xthumb}_50x50c.jpg" : null;

                }

                //var_dump($xtitle[0]);

                $listings[] = array(

                    'title'=>ucfirst($xtitle[0]->nodeValue) ?? null, //null coalescence in php 7
                    'url'=>$xtitle[0]->getAttribute('href') ?? null,
                    'br'=>trim(preg_replace('~\D~', '', $br) ?? null), //leave only numerical values
                    'cost'=>trim(preg_replace('~\D~', '', $xcost[0]->nodeValue) ?? null),
                    'location'=>$location, 
                    'picture'=>$picture,
                    'added'=>$this->nowTS
                );

            }

            }

            return $listings;

            } 
            else return; //no listings found.. error?

        }
        else return;


    }

    function saveListings($listings){

        if(is_array($listings)){

            //flag the previous listings for deletion
            $sql = "update aptlistings set `to-delete` = '1' where `added` = '{$this->metrics['last_updated']}'";
            $this->db->exec($sql);

            foreach($listings as $listing){

                $sql = "insert into aptlistings (`title`,`url`,`br`,`cost`,`location`,`picture`,`added`) values (?,?,?,?,?,?,?)";

                if(!$this->db->prepare($sql)->execute([$listing['title'],$listing['url'],$listing['br'],$listing['cost'],$listing['location'],$listing['picture'],$listing['added']])){

                    throw new \Exception('Error inserting row'); 

                }

            }

            return true;
        }
        else throw new \Exception('new listings not an array'); 


    }

    function getListings(){ //if we were doing pagination we'd pass in page number, offset, etc to only get the slice of data we need

        if($stmnt = $this->db->prepare("select * from aptlistings where `to-delete` = '0' order by `alid` asc")){

            $stmnt->execute();

            if($res = $stmnt->fetchAll(\PDO::FETCH_ASSOC)){

                return $res;

        }

        }
        
        return false; //we didn't get any listings.. error?
    }

    function updateListings(){

        $srcHtml = '';
        
        if($this->shouldUpdateListing()){
        
        //check to see if we are testing. If so we use the local file so as not to make annoying request to src server (or get blocked)
        if($srcHtml = ($this->config['testing'] ? $this->getTestHtml() : $this->makeReq($this->config['url'],'GET'))){

            $this->updateListingOpts('updating',1);

            if($newListings = $this->parseUrlContent($srcHtml)){

                if($this->saveListings($newListings)){

                    $this->updateListingOpts('updating',0);

                    $this->updateListingOpts('last_checked',$this->nowTS);

                    $this->updateListingOpts('amount',count($newListings));

                    $this->updateListingOpts('last_modified',$this->lastModifiedTS);

                    return true;
                }
                else throw new \Exception('unable to save new listings');
            }
            else throw new \Exception('unable to parse listings');
        }
        else throw new \Exception('unable to get html data');

        }
        else return;

    }

    function updateListingOpts($name,$value){

        if($opts = filter_var_array(array('name'=>$name,'value'=>$value), FILTER_SANITIZE_STRING)){

            $sql = "insert into aptopts (`name`,`value`) values (?,?) on duplicate key update value=?";

            if(!$this->db->prepare($sql)->execute([$opts['name'], $opts['value'], $opts['value']])){

                throw new \Exception('Error inserting row');  
            }

        }
        else throw new \Exception('bad input');

    }

    //check to see if we should be updating the saved listings
    function shouldUpdateListing(){

            if($stmnt = $this->db->prepare("select * from aptopts order by amid asc")){

                $stmnt->execute();

                if($res = $stmnt->fetchAll(\PDO::FETCH_ASSOC)){

                    foreach($res as $val) $this->metrics[$val['name']] = $val['value']; //get the current metrics

                if($this->metrics && $this->metrics['last_modified'] !== $this->lastModifiedTS){

                    return ($this->metrics['updating'] !== '1' && $this->metrics['last_modified'] < ($this->nowTS - ($this->config['check_interval'] * 60))) ? true : false;  

                }

            }

            }
            
            return false; //if we can't even get the opts probably a first run so lets start the process

    }


    function getTestHtml(){


        if(file_exists(__DIR__ . '/../../data/' . $this->config['test_file'])){

            $html = file_get_contents(__DIR__ . '/../../data/' . $this->config['test_file']);

        }
        else {

            //testing but file does not exist. Lets get source html and save it for next time

            if($html = $this->makeReq($this->config['url'],'GET')){

                file_put_contents(__DIR__ . '/../../data/' . $this->config['test_file'],$html);

                return $html;
            }

        }

        return $html;

    }
    //this makes the outgoing api call. It uses curl as a backup in case
    // url_wrappers are disabled
    public function makeReq($url,$method,$data=array(),$retArr=false){

        $resp = false;

        if( $this->_isWrappers()) {

            $resp = $this->makeReqHttp($url,$method,$data);
        }
        else if($this->_isCurl()){

            $resp = $this->makeReqCurl($url,$method,$data);
        }
        else throw new \Exception('unable to connect');

        if($resp){


            $jsonArr = json_decode($resp,true);

            if(!isset($jsonArr['Error'])){

                return ($retArr) ? $jsonArr : $resp;

            }
            else return false;
            //else throw new \Exception($jsonArr['Error']);
        }
        else{

            throw new \Exception('invalid json');
        }

        return;
    }

    //check if curl is enabled
    function _isCurl(){

        return function_exists('curl_version');
    }

    //check if url wrappers are enabled
    function _isWrappers(){

        return ini_get('allow_url_fopen');
    }

    //make request using http context which depends on url wrappers
    function makeReqHttp($url,$method,$data=array()){

        $resp = '';

        if($method == 'GET'){

                $opts = array('http' =>
                    array(
                        'method' => 'GET',
                        'max_redirects' => '0',
                        'ignore_errors' => '1'
                    )
                );


        $context = stream_context_create($opts);

        $stream = fopen($url, 'r', false, $context);

        // header information as well as meta data
        // about the stream
        //var_dump(stream_get_meta_data($stream));

        // actual data at $url
        $resp = stream_get_contents($stream);

        fclose($stream);

        }
        else if($method == 'POST'){

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    'content' => json_encode($data)
                )
            );
            
            $context = stream_context_create($opts);
            
            $resp = file_get_contents($url, false, $context);


        }
        return $resp;

    }

    function getLastModifiedDate(){

        if($hdrs = get_headers($this->config['url'], TRUE)){

            return $hdrs["Last-Modified"];
        }
        else $this->now;

    }

    //make request using curl (used as last resort)
    function makeReqCurl($url,$method,$data=array()){


        $resp = false;

        if($method == 'GET'){

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                                                   
            $resp = curl_exec($curl);

            if ($resp === false) {

                $info = curl_getinfo($curl);

                curl_close($curl);

                throw new \Exception('error occured during curl exec. Additional info: ' . var_export($info));
            }
            curl_close($curl);

        }
        else if($method == 'POST'){

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_POST, true);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen(json_encode($data)))                                                                     
            ); 

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); 

            $resp = curl_exec($curl);

            if ($resp === false) {

                $info = curl_getinfo($curl);

                curl_close($curl);

                throw new \Exception('error occured during curl exec. Additional info: ' . var_export($info));
            }
            curl_close($curl);

        }

        return $resp;


    }

    //is called when class unloads
    function __destruct(){}

}