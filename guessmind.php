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
$exp=trim($exp);
$exp=strtoupper($exp);

$name = str_replace(" ","_",$name);
$name=str_replace("+","",$name);
$filename=$name."GUESS_MIND.txt";
$filename2=$name."MIND_PREV.txt";

//if(file_exists($filename)!=1){

    if(strtolower($exp)=="[guess mastermind]"){
        
        $newfile=fopen($filename, "w") or die("Nope");
        for ($st = 1111; $st<= 5555; $st++){
                $s=strval($st);
            if (strpos($s, '6') === false &&strpos($s, '7') === false&&strpos($s, '8') === false&&strpos($s, '9') === false&&strpos($s, '0') === false) {
                
            fwrite($newfile,$st."\n");
            }
        }
        fclose($newfile);
        $message->message="MASTERMIND\nChoose a four digit code using 1, 2, 3, 4, 5 (Example: 2445).\n";
        $message->message.="Put 🔴 or ⚪ according to earlier rules. You can also use the letters R and W instead (only if more than one letter). Do not put any other character, including spaces.\n";
        $message->message.="\nSend 'Empty' if you have no response for my guess.\nIf my guess is correct, put RRRR or 🔴🔴🔴🔴\nYou can restart anytime by sending [guess mastermind] again.\nLet's start!\nMy guess: 1212";
        $forcode = fopen($filename2, "w") or die("nah");
        fwrite($forcode, "1212");
        fclose($forcode);
        $rf= new \stdClass();
        $rf->replies=array($message);
echo json_encode($rf);

exit();
    }


    if($exp=="🔴🔴🔴🔴"||$exp=="RRRR"){
        unlink($filename);
        unlink($filename2);
        $message->message="Sweet, got it!\nTo play again, send [guess mastermind]. To view my program code, send [code for guess m]";
      }
      else if(preg_match("/[🔴R⚪WT]+/i",$exp)==1){
      $tempRed=0; $tempWhite=0;
      $tempWhite += substr_count($exp, "⚪");
      $tempWhite += substr_count($exp, "W");
      $tempRed += substr_count($exp, "🔴");
      $tempRed += substr_count($exp, "R");
      if($exp!="EMPTY"){
      $exp="";
         
         for($i=0;$i<$tempRed;$i++){
             $exp.="R";
         }
         for($i=0;$i<$tempWhite;$i++){
             $exp.="W";
         }
         }
      
    $thefile=file($filename);
    if(count($thefile)==1){
        $message->message="Your code has to be: ".trim($thefile[0])."\nTo play again, send [guess mastermind]. To view my program code, send [code for guess m]";
        $rf=new \stdClass();
        unlink($filename);
        unlink($filename2);
$rf->replies=array($message);
echo json_encode($rf);

exit();
    }
    $prevCodeFile=file($filename2);
    $prevcode = trim($prevCodeFile[0]);
    $allowed = array();
    
    $prevc = $prevcode;
    $prevcode.="\n";
    $thefile = array_diff($thefile, array($prevcode));
    
    foreach($thefile as $code){
        $score = scoreis($prevc, $code);
        
       // echo "\n".$score;
        if(strtoupper($exp) == trim($score)){
           $allowed[]=$code;
          //echo "yes";
        }
        
    }
    
    
     if(count($allowed)==1){
        $message->message="Your code has to be: ".trim($allowed[0])."\nThink I am wrong? Check your responses first. View the rules by sending [rules mastermind]\nTo play again, send [guess mastermind]. To view my program code, send [code for guess m]\nSend [mastermind] if you want to guess my code.";
        $rf=new \stdClass();
        unlink($filename);
        unlink($filename2);
$rf->replies=array($message);
echo json_encode($rf);

exit();
    }
   
    
    $newfile=fopen($filename, "w") or die("Nope");
    foreach ($allowed as $writethis){
        fwrite($newfile, trim($writethis)."\n");
    }
    fclose($newfile);
    $limallowed = count($allowed)-1;
    $rn = rand(0, $limallowed);
    $message->message="My guess: ".$allowed[$rn];
    $forcode = fopen($filename2, "w") or die("nah");
        fwrite($forcode, $allowed[$rn]);
        fclose($forcode);
        }
        else{
        echo "error";
        }

if(trim($message->message)=="My guess:"){
        $message->message="Your responses are impossible for a constant code. Please check them again.\nIf you think you were correct in your responses, report to my developer.\nTo start over, send [guess mastermind]";
        unlink($filename);
        unlink($filename2);
        }
$rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);


function scoreis($exp, $thecode){
    $thecode = str_replace("\n","",$thecode);
    
    $thecode = trim($thecode);
    $codelength = strlen($thecode);
    $white=$red=0;
    $a=$b=$c=$d=$e=$f=$g=0;//these are the number of occurences of colors
        for($i=0;$i<$codelength;$i++){
            $ch=$thecode[$i];
            switch($ch){
                case "1":
                ++$a;
                break;
                case "2":
                ++$b;
                break;
                case "3":
                ++$c;
                break;
                case "4":
                ++$d;
                break;
                case "5":
                ++$e;
                break;
                case "6":
                ++$f;
                break;
                case "7":
                ++$g;
                break;
            }
        }
        ///now check whites
       
            if(substr_count($exp, "1")>0&&$a>0){
                $white+=min($a,substr_count($exp, "1"));
            }
            if(substr_count($exp, "2")>0&&$b>0){
                $white+=min($b,substr_count($exp, "2"));
            }
            if(substr_count($exp, "3")>0&&$c>0){
                $white+=min($c,substr_count($exp, "3"));
            }
            if(substr_count($exp, "4")>0&&$d>0){
                $white+=min($d,substr_count($exp, "4"));
            }
            if(substr_count($exp, "5")>0&&$e>0){
                $white+=min($e,substr_count($exp, "5"));
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
             $w.="W";
         }
         }
         if($red>0){
         for($i=0;$i<$red;$i++){
             $r.="R";
         }
         }
         $ret = $r.$w;
        // echo $white;
        if($white==0&&$red==0){
        return "EMPTY";
        }
        else{
         return ($ret);
         }
    }
?>
