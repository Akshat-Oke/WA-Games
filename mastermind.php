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
$filename=$name."MIND.txt";
$colors=array("A", "B", "C", "D", "E", "F", "G");
if(file_exists($filename)!=1){
    if(strtolower($exp)=="[mastermind]"){
        $thecode="";
        $totalc=count($colors)-1;
        for($i=1;$i<=5;$i++){
        $n=rand(0,$totalc);
        $thecode.=$colors[$n];
        }
        $newfile=fopen($filename, "w") or die("Nope");
        fwrite($newfile,$thecode."\n0");
        fclose($newfile);
        $message->message="MASTERMIND\nI have chosen a 5 colored code using any of the 7 colors A, B, C, D, E, F, G. Start by putting your guess.\nExample: 'ABCDE'.";
    }
}
else{
    $thefile=file($filename);
    $thecode=trim($thefile[0]);
    $codelength=strlen($thecode);
    $attempts=(int)$thefile[1];
    $white=0;
    $red=0;
    $exp=strtoupper($exp);
    if($exp=="RESET M"){
    unlink($filename);
    $message->message="MASTERMIND has been reset. Send [mastermind] to start over.";
    }
    else if($exp=="REVEAL CODE"){
    
    $message->message="The MASTERMIND code for this game is ".$thecode."\nYou can still continue the game.\nFor a new code, send 'reset m'.";
    }
    else if($exp!=$thecode){
        $attempts+=1;
        $a=$b=$c=$d=$e=$f=$g=0;//these are the number of occurences of colors
        for($i=0;$i<$codelength;$i++){
            $ch=$thecode[$i];
            switch($ch){
                case "A":
                ++$a;
                break;
                case "B":
                ++$b;
                break;
                case "C":
                ++$c;
                break;
                case "D":
                ++$d;
                break;
                case "E":
                ++$e;
                break;
                case "F":
                ++$f;
                break;
                case "G":
                ++$g;
                break;
            }
        }
        ///now check whites
       
            if(substr_count($exp, "A")>0&&$a>0){
                $white+=min($a,substr_count($exp, "A"));
            }
            if(substr_count($exp, "B")>0&&$b>0){
                $white+=min($b,substr_count($exp, "B"));
            }
            if(substr_count($exp, "C")>0&&$c>0){
                $white+=min($c,substr_count($exp, "C"));
            }
            if(substr_count($exp, "D")>0&&$d>0){
                $white+=min($d,substr_count($exp, "D"));
            }
            if(substr_count($exp, "E")>0&&$e>0){
                $white+=min($e,substr_count($exp, "E"));
            }
            if(substr_count($exp, "F")>0&&$f>0){
                $white+=min($f,substr_count($exp, "F"));
            }
            if(substr_count($exp, "G")>0&&$g>0){
                $white+=min($g,substr_count($exp, "G"));
            }
            for($i=0;$i<$codelength;$i++){
            if($exp[$i]==$thecode[$i]){
                ++$red;
                --$white;
            }
            }
        
         $w="";
         $r="";
         if($white>0){
         for($i=0;$i<$white;$i++){
             $w.="⚪";
         }
         }
         if($red>0){
         for($i=0;$i<$red;$i++){
             $r.="🔴";
         }
         }
         $message->message="You put: ".$exp."\nAttempt ".$attempts."\nResponse: \n".$w."\n".$r;
         $openfile=fopen($filename, "w") or die("Could not open");
         fwrite($openfile, $thecode."\n".$attempts);
         fclose($openfile);

         if($attempts==12){
         $message->message.="\n12 attempts used already! Try to be more efficient in your guesses.";
            }
    }
    else if($exp==$thecode){
        $thefile=file($filename);
        $thecode=trim($thefile[0]);
        $codelength=strlen($thecode);
        $attempts=(int)$thefile[1];
        $message->message="Correct! Code was ".$thecode."\nAttempts taken: ".$attempts."\nGame over. \nSend [mastermind] to start again! Send [code for mastermind] to view my computer code for it";
        $message->message.="\n\nIt is now my turn to guess your code! Send [guess mastermind] including brackets to begin.";
        unlink($filename);
    }
    
}

$rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);
?>
