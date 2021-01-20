<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 60");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$message= new \stdClass();
//Default is here
/*
$json=file_get_contents('php://input');
$data = json_decode($json);
$exp = $data->query->message;
$name = $data->query->sender;
*/
$exp=$_GET['n'];
$name=$_GET['name'];

$name = str_replace(" ","_",$name);
$name=str_replace("+","",$name);

$filename=$name."CODE.txt";

if(preg_match("/.*\[compile\].*/i", $exp)){
    if(file_exists($filename)==1){
        $message->message="Your previous code has been deleted. Please send your language:\n java\n cpp\n c\n c99\n cpp14\n php\n perl\n python2\n python3\n ruby\n go\n scala\n bash\n sql\n pascal\n csharp\n swift\n rust\n r\n nodejs\n kotlin\n nodejs\n kotlin";
        $newfile = fopen($filename, "w") or die("nah");
        fwrite($newfile, $exp);
        fclose($newfile);
    }
    else{
        $message->message="New code saved. Please send your language:\n java\n cpp\n c\n c99\n cpp14\n php\n perl\n python2\n python3\n ruby\n go\n scala\n bash\n sql\n pascal\n csharp\n swift\n rust\n r\n nodejs\n kotlin\n nodejs\n kotlin";
        $exp = preg_replace("/\[compile\]/i", "", $exp);
       $newfile = fopen($filename, "w") or die("nah");
        fwrite($newfile, $exp);
        fclose($newfile);
    }
}
else {
    if(file_exists($filename)==1){
         $url = "https://api.jdoodle.com/v1/execute";
$obj = new \stdClass();
$obj->clientId = "{my_id_here}";
$obj->clientSecret = "{my_secret_here}";

$obj->script=file_get_contents($filename);

$obj->stdin="";
$obj->language=strtolower($exp);
$obj->versionIndex=0;
$content = json_encode($obj);
//echo json_encode("<ghj ");
//echo $content;

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

$json_response = curl_exec($curl);
echo $json_response;
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

/*if ( $status != 201 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}*/

curl_close($curl);
$json = json_decode($json_response);
$message->message= "Output:\n". $json->output;
    }
}




$rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);
?>
