<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$message= new \stdClass();
//Default is here

$json=file_get_contents('php://input');
$data = json_decode($json);
$exp = $data->query->message;
$name = $data->query->sender;
$name = str_replace(" ","_",$name);
$name=str_replace("+","",$name);
//$exp=$_GET['n'];
//$name=$_GET['name'];
$filename=$name.".txt";

if(file_exists($filename)!=1){
$number = mt_rand(1,100);
$newfile=fopen($filename, "a") or die("Nope");
fwrite($newfile, $number);
fclose($newfile);
}
$exp=(int)$exp;
$thefile=file($filename);
$thenum=$thefile[0];
if(count($thefile)==1){
if(1<=$exp&&$exp<=100 && $exp!=$thenum && count($thefile)<2){
    if(($thenum-$exp)<=10 && ($thenum-$exp)>=-10){
        $message->message="Warm! \n A new number has been chosen by me.";
    }
    else{
        $message->message="Cold! A new number has been chosen by me.";
    }
    $newfile=fopen($filename, "a") or die("Nope");
    $e="\n".$exp;
fwrite($newfile,$e);
fclose($newfile);
}
else if($exp==$thenum && count($thefile)<2){
    $message->message="Correct! Very lucky! Number was ".$thenum." To view the code, send 'code'.";
}
else {
    $message->message="Out of bounds!";
}
}
else{
    if(1<=$exp&&$exp<=100 && $exp!=$thenum){
    $oldnum=$thefile[1];
    $old_diff=abs($oldnum-$thenum);
    $new_diff=abs($exp-$thenum);

    if($new_diff<$old_diff){
        $message->message="Warmer!";
    }
    else {
        $message->message="Colder!";
    }
    }
    else if($exp!=$thenum){
        $message->message="Out of bounds!";
    }
    else{
        $message->message="Correct! Number was ".$thenum."To play again, send 'DA'. \n  View the code by sending 'code'.\nAnd now it's your turn. Send [start guessing] to begin, or send [rules] to view rules.";
        unlink($filename);
    }
    //$thefile[1]=$exp;
    $contents=$thenum.$exp;
    file_put_contents($filename, $contents);
}

//$message->fulfillmentMessages=array($text);
//$message->message="Yo there, jeenius.cf here!";
//$q = $data->queryResult->queryText;

$rf=new \stdClass();
//$t=$page->getElementsByTagName("head")->item(0)->getElementsByTagName("title")->item(0)->nodeValue;

$rf->replies=array($message);
echo json_encode($rf);
?>
