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
$origexp=$exp;

$dont=0;
$yes=0;
if (strpos($writethis, '/') !== false) {
$exp=str_replace("//", " ", $exp);
}
else{
$exp=preg_replace("/[^A-Za-z_ ]/", '', $exp);
}

$filename=$name."MAN.txt";

$easy=array("house", "water", "cannon", "football", "bucket", "dwarf", "strength");

$moderate=array("affix", "axiom", "rhythm", "jackpot", "equip", "injury", "aquarium", "oxygen", "jaundice", "mystify", "puzzling", "sphinx");

$hard=array("phlegm", "fuchsia", "exodus", "askew", "abyss", "espionage", "quixotic");
$hollyact=array("robert de niro", "morgan freeman", "leonardo dicaprio", "brad pitt", "tom hanks", "tom cruise");
if(file_exists($filename)!=1){
    if(strtolower($origexp)=="[hangman]"){
    $message->message="HANGMAN\nSelect difficulty/category of word:\nEasy(7 words)\nModerate(12 words)\nHard(7 words)";
    $h=fopen($filename, "w") or die("nope");
    fwrite($h, "STARTING");
    fclose($h);
    $rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);
exit();
    }
    }
    else if(file_exists($filename)==1){
    if(strtoupper($exp)=="RESET"){
            $message->message="Game has been reset. Send [hangman] to start again.";
    unlink($filename);
    $rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);
exit();
}
    $r=file($filename);
    if(trim($r[0])=="STARTING"){
    $dont=1;
        if(strtoupper($exp)=="EASY"){
            $newfile=fopen($filename,"w") or die("Nope");
            $t=count($easy)-1;
            $number = mt_rand(0,$t);
            fwrite($newfile, $easy[$number]);
            fwrite($newfile, "\n10");
            fclose($newfile);
            $message->message="I have chosen a word of Easy difficulty. Send a letter to begin!";
            $message->message.="\nSend a letter you think might be in the word. You can also send the whole word if you think it is correct!";
            $message->message.="\nSend 'reset' to reset the game.";
        }
        else if(strtoupper($exp)=="MODERATE"){
            $newfile=fopen($filename,"w") or die("Nope");
            $t=count($moderate)-1;
            $number = mt_rand(0,$t);
            fwrite($newfile, $moderate[$number]);
            fwrite($newfile, "\n10");
            fclose($newfile);
            $message->message="I have chosen a word of ".$exp." difficulty. Send a letter to begin!";
            $message->message.="\nSend a letter you think might be in the word. You can also send the whole word if you think it is correct!";
            $message->message.="\nSend 'reset' to reset the game.";
        }
        else if(strtoupper($exp)=="HARD"){
            $newfile=fopen($filename,"w") or die("Nope");
            $t=count($hard)-1;
            $number = mt_rand(0,$t);
            fwrite($newfile, $hard[$number]);
            fwrite($newfile, "\n10");
            fclose($newfile);
            $message->message="I have chosen a word of ".$exp." difficulty. Send a letter to begin!";
            $message->message.="\nSend a letter you think might be in the word. You can also send the whole word if you think it is correct!";
            $message->message.="\nSend 'reset' to reset the game.";
        }
        else if(strtoupper($exp)=="H_ACTORS"){
            $newfile=fopen($filename,"w") or die("Nope");
            $t=count($hollyact)-1;
            $number = mt_rand(0,$t);
            fwrite($newfile, $hollyact[$number]);
            fwrite($newfile, "\n10");
            fclose($newfile);
            $message->message="I have chosen one of the Hollywood Actors. Send a letter to begin!";
            $message->message.="\nSend a letter you think might be in the word. You can also send the whole word if you think it is correct!";
            $message->message.="\nSend 'reset' to reset the game.";
        }
        else{
            $message->message=$exp." is an invalid category.";
        }
        }//starting end
    }//file exists and starting end

if(file_exists($filename)==1&&$dont==0){
    $thefile=file($filename);
    $theword=trim($thefile[0]);
    $lives=(int)$thefile[1];
    $words="\n";
    for($i=2;$i<count($thefile);$i++){
    $words.=$thefile[$i];
    }
    $words.="\n×".$exp."×";
    $wordarray=str_split($theword);
    $writethis="";
    $yes=0;
    if(strtoupper($exp)==strtoupper($theword)){
            $message->message="Correct! The word was ".$theword."\nTo play again, send [hangman]! To view the code, send [code for hangman].";
            unlink($filename);
            $rf=new \stdClass();
        $rf->replies=array($message);
        echo json_encode($rf);
        exit();
     }//word was fully correct
     else if(strlen(trim($exp))>1){
        if (strpos($exp, ' ') !== false) {}
        else{
        $message->message=$exp." is wrong! Try again. Lives remaining: ".$lives." No lives lost.";
        $rf=new \stdClass();
        $rf->replies=array($message);
        echo json_encode($rf);
        exit();
        }
        }//word was not correct
     else if(strlen(trim($exp))==1){//letter has been guessed
    for($i=0; $i<count($wordarray);$i++){
        if(strtolower($wordarray[$i])==strtolower($exp)||ctype_upper($wordarray[$i])){
            $writethis.=$theword[$i]." ";
            $theword[$i]=strtoupper($theword[$i]);//convert the letter to uppercase when it is guessed correctly
            if(strtolower($wordarray[$i])==strtolower($exp)){
                $yes=1;//if the letter was correct
            }
            else if(strtolower($wordarray[$i])!=strtolower($exp)){

                //$yes=0;
            }//otherwise $yes=1 since earlier responses had matched
        }
        else{
                //if($yes!=1)
                //$yes=0;
                $writethis.="_"." ";
        }
    }
    if($yes==0){
            $lives=$lives-1;
            $myfile=fopen($filename, "w") or die("Unable to open file");
            fwrite($myfile, $theword."\n".$lives.$words);
            fclose($myfile);
            //$writethis=strtolower($writethis);
            $writethis.="\nLives remaining: ".$lives."\n".$words;
            if($lives==0){
                $message->message="Game over! ".$theword;
                $writethis="Game over! Correct answer: ".$theword;
            }
      }//yes==0 over
      else if($yes==1){
            $myfile=fopen($filename, "w") or die("Unable to open file");
            fwrite($myfile, $theword."\n".$lives.$words);
            fclose($myfile);
    $writethis.="\nLives remaining: ".$lives."\n".$words;
    
    $message->message=$writethis;
    }
    if($lives==0){
     $message->message="Game over! ".$theword;
     $writethis="Game over! Correct answer: ".$theword;
     }
    }
    //check if all letters have been guessed or not
    if (strpos($writethis, '_') !== false) {}
    else {
    $writethis.="\n*".$theword."*\nGood job! All letters guessed correctly with ".($lives+1)." lives to spare. Send [hangman] to play again. Send [code for hangman] to view the code!";
    unlink($filename);
    }
    $message->message=$writethis;
}

$rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);
?>
