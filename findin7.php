<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 60");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$message= new \stdClass();
//Default is here

$json=file_get_contents('php://input');
$data = json_decode($json);
$exp = $data->query->message;
$name = $data->query->sender;
/*
$exp=$_GET['n'];
$name=$_GET['name'];
*/
$name = str_replace(" ","_",$name);
$name=str_replace("+","",$name);

$filename=$name."7guesses.txt";
if(strtoupper($exp)=="RESET 7"){
        unlink($filename);
        $message->message="Find in 7 game has been reset. Start over by sending [find in 7], including brackets.";
        $rf=new \stdClass();
        $rf->replies=array($message);
        echo json_encode($rf);
        exit();
}
if(file_exists($filename)!=1){
    if(strtolower($exp)=="[find in 7]"){
        $message->message="Choose a number between 0 and 127.\n Answer the following 7 questions with 'Yes' or 'No'.\n";
        $message->message.="Is your number in this list?\n";
        $newfile=fopen($filename, "w") or die("Nope");
        fwrite($newfile, "STARTING");
        fclose($newfile);
        for($i=1;$i<=127;$i++){
            if($i%2==1){
                $message->message.=$i." ";
            }
        }
    }
}
else{
    $thefile=file($filename);
    $stage=count($thefile);
    if($stage<7){
        $message->message="Is your number in this list? Send 'Yes' or 'No'.\n";
        if($stage==1&&trim($thefile[0])=="STARTING"){
        
        }
        else{
        ++$stage;}
        $div=pow(2,$stage);
        
        if($div>100){
        if(strtoupper($exp)=="YES"){
            $myfile=fopen($filename, "a") or die("Could not open");
            fwrite($myfile, "\n1");
            fclose($myfile);
        }
        else{
            $myfile=fopen($filename, "a") or die("Could not open");
            fwrite($myfile, "\n0");
            fclose($myfile);
        }
        $thefile=file($filename);
    $stage=count($thefile);
         $thenum=0;
        for($i=0;$i<$stage;$i++){
            $p=(int)$thefile[$i];
            $add=(pow(2, $i))*$p;
            $thenum+=$add;
        }
        unlink($filename);
       
        $message->message="Your number is ".$thenum.".\nTo play again, send [find in 7]. To view the code, send [code for 7]";
        
        }
                
        else{
        for($i=1; $i<128;$i++){
            if(($i & $div)!=0){
                $message->message.=$i." ";
            }
        }
        if($stage==1&&trim($thefile[0])=="STARTING"){
        if(strtoupper($exp)=="YES"){
            $myfile=fopen($filename, "w") or die("Could not open");
            fwrite($myfile, "1");
            fclose($myfile);
        }
        else{
            $myfile=fopen($filename, "w") or die("Could not open");
            fwrite($myfile, "0");
            fclose($myfile);
        }
    }
        else{
        if(strtoupper($exp)=="YES"){
            $myfile=fopen($filename, "a") or die("Could not open");
            fwrite($myfile, "\n1");
            fclose($myfile);
        }
        else{
            $myfile=fopen($filename, "a") or die("Could not open");
            fwrite($myfile, "\n0");
            fclose($myfile);
        }
        }
    }
    }
    else{
        /*$thenum=0;
        for($i=0;$i<$stage;$i++){
            $p=(int)$thefile[$i];
            $add=(pow(2, $i))*$p;
            $thenum+=$add;
        }
        unlink($filename);
        $message->message="Your number is ".$thenum." To play again, send [find in 7]. To view the code, send [code for 7]";
    */
    }
}
$rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);
?>
