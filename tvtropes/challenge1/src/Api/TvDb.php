<?php

namespace App\Api;

class TvDb 
{

    //holds the api token
    private $jwt;

    public $errorMsg;

    //holds the app config
    private $config;

    private $db;

    private $now;

    private $nowTS;

    public function __construct($conf){

        if(!is_array($conf)){

            throw new \Exception('config options not found');

        }
        else {
            
            $this->config = $conf;

            $this->db = $this->getDB();
        
            $this->jwt = $_SESSION['jwt'] = $this->getJwt();

            $this->now = date("Y-m-d H:i:s");

            $this->nowTS = time();

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


    //fetches token from database
    function getDbToken(){

        if($stmnt = $this->db->prepare("select * from jwt where id=?")){

            $stmnt->execute(['1']);

            $jwtData = $stmnt->fetch();

            $jwtToken = json_decode($jwtData['jwttoken'],true);

            $jwtDate = $jwtData['date_updated'];

            return array('token'=>$jwtToken['token'],'date_updated'=>$jwtDate);

        }
        else {

            throw new \Exception('unable to get token from db');

            return false;
        }
    }

    function isTokenValid($last_updated,$ts=false){

        $last_updated_ts = (!$ts) ? strtotime($last_updated) : $last_updated;

        return ($last_updated_ts < ($this->nowTS - $this->config['jwt']['refresh']));

    }

    //manages the api token
    function getJwt(){

        if(isset($_SESSION['jwt']) && !empty($_SESSION['jwt']['token']) && ($this->isTokenValid($_SESSION['jwt']['date_updated']))){

            return $_SESSION['jwt'];
        }
        else {

            if($jwtData = $this->getDbToken()){

                
                //token still valid
                if(!empty($jwtData['jwttoken']) && $this->isTokenValid($jwtData['date_updated'])){

                    //refresh token

                    $this->jwtRefresh();

                    return array('token'=>$jwtToken,'date_updated'=>$jwtDate);
                }
                else { //token is expired or does not exist have to get another


                    return $this->jwtLogin();

                }

            }
            else throw new \Exception('Unable to get jwt');

            
        }

        return;
    }

    //refreshes the token
    function jwtRefresh(){

        $urlPath = '/refresh_token?token=' . $this->jwt['token'];

        if ($resp = $this->makeReq($this->config['jwt']['endpoint'] . $urlPath, 'GET')) {

            $sql = "update `jwt` set date_updated = ?, jwttoken = ? where id=?";

            $this->db->prepare($sql)->execute([$this->now, $resp, '1']);

            return $resp;
        }
        else {

            throw new \Exception('error refreshing jwt token');

            return;
        }

    }

    //checks json string for validity
    function isValidJson($data) {

        if (!empty($data)) {
                
            json_decode($data);
                      
            return (json_last_error() === JSON_ERROR_NONE);
              
        }
              
        return;
      }

      //retrieve saved episodes data
      //$type is a field in the table series ie episodes
    function getEpisodeData($id,$type=null){

        if(filter_var($id,SANITIZE_NUMBER_INT)){


            if($stmnt = $this->db->prepare("select * from series where id=?")){

                $stmnt->execute([$id]);
    
                $series = $stmnt->fetch();

                
                return $series[$type] ?? $series;

            }
            else return;


        }
        else throw new \Exception('bad input');

        return;


    }

      //not implemented: purpose is to save retrieved episodes 
    function saveSeries($id){

        if(filter_var($id,SANITIZE_NUMBER_INT)){

            $sql = "insert into seriess (`id`,`jwttoken`,`date_added`) values (?,?,NOW()) on duplicate key update date_updated=NOW()";

            $this->db->prepare($sql)->execute(['1', $resp]);

            return $this->getDbToken();

    }
    else throw new \Exception('bad input');

    return;

    }


    //logs in and gets a new token
    function jwtLogin(){

        unset($_SESSION['jwt']);

        unset($this->jwt);

        if($resp = $this->makeReq($this->config['jwt']['endpoint'] . '/login','POST',$this->config['jwtcreds'])){

        
            $sql = "insert into jwt (`id`,`jwttoken`,`date_updated`) values (?,?,NOW()) on duplicate key update date_updated = NOW()";

            $err = $this->db->prepare($sql)->execute(['1', $resp]);

            return $this->getDbToken();
        }
        else {

            throw new \Exception('error getting jwt token');


        }
            return;
    }


    //retrieves series from api
    function searchSeries($term){

        //sanitize string
        $term = filter_var($term, FILTER_SANITIZE_STRING);

        if ($data = $this->makeReq($this->config['jwt']['endpoint'] . '/search/series?name=' . urlencode($term), 'GET', array(),true)){

            foreach($data['data'] as $key => $val) {

                if($imgData = $this->getSeriesImages($val['id'],true)){
                    
                   $data['data'][$key]['_img'] = !empty($imgData['data'][0]['fileName']) ? $this->config['jwt']['imgprefix'] . $imgData['data'][0]['fileName'] : './images/noimg.png';
    
                }
                else $data['data'][$key]['_img'] = './images/noimg.png';

                //set timestamp when data was received

                $data['data']['data'][$key]['last_updated'] = $this->nowTS;

                $episodes = $this->getEpisodes($val['id'],true);

                //get episodes
                $data['data'][$key]['_episodes'] = ($episodes) ? array_slice($episodes,-3,3) : array();


            }

            //save the current search result to the session
            return $_SESSION['searchedSeries'] = json_encode($data);

        }
        else {

            throw new \Exception('unable to get data from API');
        }

        return;

    }

    //get series information
    function getSeriesInfo($seriesId){

        if($resp = $this->makeReq($this->config['jwt']['endpoint'] . '/series/' . $seriesId,'GET')){

            return $resp;

        }
        else {

            throw new \Exception('unable to get data from API');
        }

        return;
    }


    //get series images
    function getSeriesImages($seriesId,$retArr=false){

        if($resp = $this->makeReq($this->config['jwt']['endpoint'] . '/series/' . $seriesId . '/images/query?keyType=poster','GET',array(),$retArr)){

            return $resp;

        }
        //some series do not images it seems
       // else {
        
           // throw new \Exception('unable to get data from API');

       // }

        return;
    }

    //is called when class ends
    function __destruct(){}


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

        if($this->isValidJson($resp)){


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

            if(isset($this->jwt['token'])){
                $opts = array('http' =>
                    array(
                        'method' => 'GET',
                        'header'  => 'Authorization: Bearer ' . $this->jwt['token'], 
                        'max_redirects' => '0',
                        'ignore_errors' => '1'
                    )
                );
            }
            else {
                $opts = array('http' =>
                    array(
                        'method' => 'GET',
                        'max_redirects' => '0',
                        'ignore_errors' => '1'
                    )
                );

            }


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
            
            if(isset($this->jwt['token'])){

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json, Authorization: Bearer ' . $this->jwt['token'], 
                    'content' => json_encode($data)
                )
            );

        }

        else {


            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json',
                    'content' => json_encode($data)
                )
            );

        }
            
            $context = stream_context_create($opts);
            
            $resp = file_get_contents($url, false, $context);


        }
        return $resp;

    }

    //make request using curl (used as last resort)
    function makeReqCurl($url,$method,$data=array()){


        $resp = false;

        if($method == 'GET'){

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            if(isset($this->jwt['token'])){

                curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
                    'Authorization: Bearer ' . $this->jwt['token']
                    )                                                                     
                );

            }
                                                                   
            $resp = curl_exec($curl);

            if ($resp === false) {

                $info = curl_getinfo($curl);

                curl_close($curl);

                throw new \Exception('error occured during curl exec. Additioanl info: ' . var_export($info));
            }
            curl_close($curl);

        }
        else if($method == 'POST'){

            $curl = curl_init($url);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_POST, true);

            if(isset($this->jwt['token'])){

                curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                    'Content-Type: application/json',                                                                                
                    'Content-Length: ' . strlen(json_encode($data)),
                    'Authorization: Bearer ' . $this->jwt['token'])                                                                     
                ); 

            }
            else {

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: application/json',                                                                                
                'Content-Length: ' . strlen(json_encode($data)))                                                                     
            ); 
        }

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data)); 

            $resp = curl_exec($curl);

            if ($resp === false) {

                $info = curl_getinfo($curl);

                curl_close($curl);

                throw new \Exception('error occured during curl exec. Additioanl info: ' . var_export($info));
            }
            curl_close($curl);

        }

        return $resp;


    }

    //used json_encode as objects (vs arrays) to show flexibility
    function getEpisodes($seriesId,$retArr=false){

        $output = [];

        if ($resp = $this->makeReq($this->config['jwt']['endpoint'] . '/series/' . $seriesId . '/episodes', 'GET')) {

            $decoded = json_decode($resp);
  
            foreach ($decoded->data as $episode) {

                //episodes/seriedid/{id}.jpg
                $img = !empty($episode->filename) ? $this->config['jwt']['imgprefix'] . $episode->filename : './images/noimg.png';

                $output[] = [
                    'description' => $episode->overview,
                    'img' => $img,
                    'title' => $episode->episodeName,
                ];
            }
            return ($retArr) ? $output : json_encode($output);

        }
        //else {

         //   throw new \Exception('unable to get data from API');
        //}

        return;
    }

}