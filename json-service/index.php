<?php
// Report simple running errors
error_reporting(E_ERROR | E_PARSE);
require_once("config.php");
require_once("lib/dblib.php");
require_once("lib/gglib.php");
$dbh=db_connect($CFG->dbhost,$CFG->dbname,$CFG->dbuser,$CFG->dbpass);
db_query("USE ".$CFG->dbname.";", $dbh);

$GLOBALS["DBH"]=$dbh;

define("RDFAPI_INCLUDE_DIR", "C:/Apache Software Foundation/Apache2.2/rest/lib/rdfapi-php/api/");
include(RDFAPI_INCLUDE_DIR . "RDFAPI.php");
//------------------------------------------header end---------------------------------------------

function getJSON($data,$measLabel="",$station=""){
    $some_link = 'http://www.envdimosthes.gr/deltioPop.php?date='.$data;

$xq=array("gentoday"=>"/html/body/table/tr[5]/td/table/tr[2]/td[1]/b[2]","genyest"=>"/html/body/table/tr[5]/td/table/tr[3]/td[1]/b[2]",
    "so2_1"=>"/html/body/table/tr[7]/td/table/tr[3]/td[2]","so2_2"=>"/html/body/table/tr[7]/td/table/tr[3]/td[3]","so2_3"=>"/html/body/table/tr[7]/td/table/tr[3]/td[4]","so2_4"=>"/html/body/table/tr[7]/td/table/tr[3]/td[5]","so2_5"=>"/html/body/table/tr[7]/td/table/tr[3]/td[6]","so2_6"=>"/html/body/table/tr[7]/td/table/tr[3]/td[7]",
    "pm10_1"=>"/html/body/table/tr[7]/td/table/tr[4]/td[2]","pm10_2"=>"/html/body/table/tr[7]/td/table/tr[4]/td[3]","pm10_3"=>"/html/body/table/tr[7]/td/table/tr[4]/td[4]","pm10_4"=>"/html/body/table/tr[7]/td/table/tr[4]/td[5]","pm10_5"=>"/html/body/table/tr[7]/td/table/tr[4]/td[6]","pm10_6"=>"/html/body/table/tr[7]/td/table/tr[4]/td[7]",
    "co_1"=>"/html/body/table/tr[7]/td/table/tr[5]/td[2]","co_2"=>"/html/body/table/tr[7]/td/table/tr[5]/td[3]","co_3"=>"/html/body/table/tr[7]/td/table/tr[5]/td[4]","co_4"=>"/html/body/table/tr[7]/td/table/tr[5]/td[5]","co_5"=>"/html/body/table/tr[7]/td/table/tr[5]/td[6]","co_6"=>"/html/body/table/tr[7]/td/table/tr[5]/td[7]",
    "no2_1"=>"/html/body/table/tr[7]/td/table/tr[6]/td[2]","no2_2"=>"/html/body/table/tr[7]/td/table/tr[6]/td[3]","no2_3"=>"/html/body/table/tr[7]/td/table/tr[6]/td[4]","no2_4"=>"/html/body/table/tr[7]/td/table/tr[6]/td[5]","no2_5"=>"/html/body/table/tr[7]/td/table/tr[6]/td[6]","no2_6"=>"/html/body/table/tr[7]/td/table/tr[6]/td[7]",
    "o3_1"=>"/html/body/table/tr[7]/td/table/tr[7]/td[2]","o3_2"=>"/html/body/table/tr[7]/td/table/tr[7]/td[3]","o3_3"=>"/html/body/table/tr[7]/td/table/tr[7]/td[4]","o3_4"=>"/html/body/table/tr[7]/td/table/tr[7]/td[5]","o3_5"=>"/html/body/table/tr[7]/td/table/tr[7]/td[6]","o3_6"=>"/html/body/table/tr[7]/td/table/tr[7]/td[7]",
    "ypm10_1"=>"/html/body/table/tr[7]/td/table/tr[8]/td[2]","ypm10_2"=>"/html/body/table/tr[7]/td/table/tr[8]/td[3]","ypm10_3"=>"/html/body/table/tr[7]/td/table/tr[8]/td[4]","ypm10_4"=>"/html/body/table/tr[7]/td/table/tr[8]/td[5]","ypm10_5"=>"/html/body/table/tr[7]/td/table/tr[8]/td[6]","ypm10_6"=>"/html/body/table/tr[7]/td/table/tr[8]/td[7]",
    );
$dom = new DOMDocument;
$dom->preserveWhiteSpace = false;
@$dom->loadHTMLFile($some_link);
 $xpath = new DOMXPath($dom); 
 
foreach ($xq as $key=>$val){
if (($measLabel!="")&&($station!="")){
 if (($key==$measLabel."_".$station)||($key==$measLabel)){
     $entries = $xpath->query($val);
     foreach ($entries as $entry) {
 $res[$key]=$entry->nodeValue ;
}
 }
    
}else{   
$entries = $xpath->query($val);

foreach ($entries as $entry) {
 $res[$key]=$entry->nodeValue ;
}
}
}
unset($dom);

if (($measLabel!="")&&($station!="")){ //an kalesthke apo to getweek
       $response=  $res;
}else{
    $response=  json_encode($res);
}
    return $response;
}

function getWeeklyJSON($meas,$station,$days=7){
$xq=array("gentoday","genyest","so2","pm10","co","no2","o3","ypm10" );   
if ((array_search($meas,$xq)===FALSE)||($station=="")){
    return false;
}
for ($i=0;$i<$days;$i++){
    $data = date("Y-m-d", strtotime("-".$i." days"));
    $tempres=getJSON($data, $meas, $station);
 
        $res[$data]=$tempres;
 
}
    $response=  json_encode($res);
    return $response;
}






class Request {
    public $url_elements;
    public $verb;
    public $parameters;
    public $format;
    public function __construct() {
        $this->verb = $_SERVER['REQUEST_METHOD'];
        $sqs=  explode('&',$_SERVER['QUERY_STRING']);
        $this->url_elements = explode('/', $sqs[0]);
        
          $this->parseIncomingParams();
        // initialise json as default format
       // $this->format = 'json';
        if(isset($this->parameters['format'])) {
            $this->format = $this->parameters['format'];
        }
        return true;
    }
 
    public function parseIncomingParams() {
        $parameters = array();
 
        // first of all, pull the GET vars
        if (isset($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $parameters);
        }
 
        // now how about PUT/POST bodies? These override what we got from GET
     //    parse_str(file_get_contents('php://input'), $body);
       $body = file_get_contents("php://input");
        $content_type = false;
        if(isset($_SERVER['CONTENT_TYPE'])) {
            $content_type = $_SERVER['CONTENT_TYPE'];
        }
        switch($content_type) {
            case "application/json":
                $body_params = json_decode($body);
                if($body_params) {
                    foreach($body_params as $param_name => $param_value) {
                        $parameters[$param_name] = $param_value;
                    }
                }
             
                $this->format = "json";
                break;
            case "application/x-www-form-urlencoded":
                parse_str($body, $postvars);
                foreach($postvars as $field => $value) {
                    $parameters[$field] = $value;
                }
                $this->format = "html";
                break;
            default:
                  $this->format= "NO CONTENT TYPE";
                   $parameters["allbody"]=$body;
                // we could parse other supported formats here
                break;
        }
        $this->parameters = $parameters;
    }
}


$r=new Request;

if($r->parameters['allbody']!=""){
//if (!simplexml_load_string($r->parameters['allbody'])) {
    if (false){
  echo "XML not valid: load error";
  exit();
}else{
  //  echo "XML valid";
$data=$r->parameters["date"];
    }
}
//var_dump($data->pAMKA);
//echo "<br>".$data->pAMKAURI."<hr>";

//var_dump($r->format);

$uri=$r->url_elements;
//var_dump($uri);
//var_dump($r->parameters);

if ($uri[1]=="json"){    
    echo getJSON($r->parameters["date"]);
}else if (($uri[1]=="jsonSpecific")&&($r->parameters["meas"]!="")&&($r->parameters["days"]!="")){
    $days=min($r->parameters["days"],30);
   $res=getWeeklyJSON($r->parameters["meas"],$r->parameters["station"],$days) ;
   if ($res!==FALSE){
       echo $res;
   }else{
       http_response_code(400);
   }
}else{
    http_response_code(400);
}




 
?>