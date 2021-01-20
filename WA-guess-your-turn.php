<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
/*The BETWEEN syntax!:
40-->Lower bound
60-->Upper bound
50-->The current guess
40-->previous guess
*/
$message= new \stdClass();
//Default is here

$json=file_get_contents('php://input');
$data = json_decode($json);
$exp = $data->query->message;
$name = $data->query->sender;

//$exp=$_GET['n'];
//$name=$_GET['name'];

$name = str_replace(" ","_",$name);
$name=str_replace("+","",$name);
$filename=$name."YNUM.txt";
if(strtoupper($exp)=="CORRECT"||strtoupper($exp)=="END GAME"){
        if(file_exists($filename)==1){
        unlink($filename);
        $message->message="Sweet, got it! Send [start guessing] to play again! Send [code for guess] to view the code!";
        }
        else{
        $message->message="Hmm.";
        }
 }
else if(strtolower($exp)=="[start guessing]"){
    if(file_exists($filename)!=1){
    $newfile = fopen($filename, "w") or die("Failed to create file");
    $message->message="Let's go! Put 'Warmer' or 'Colder'. \nSay 'Correct' if I guess it right! For complete rules, send [rules]. My guess: 50";
    fwrite($newfile, "50");
    fclose($newfile);
    }
    else{
        $newfile = fopen($filename, "w") or die("Failed to create file");
    $message->message="*Note:* A new game has started.\n Let's go! Put 'Warmer' or 'Colder'. For complete rules, send [rules]. My guess: 50";
    fwrite($newfile, "50");
    fclose($newfile);
    }
}
else{//handle the game now
$exp=strtoupper($exp);
$thefile = file($filename);//reads file into array, each entry being a line

    if(count($thefile)<2 && trim($thefile[0])!="CHECK UP 40"){//this is for the first guess
        if($exp=="WARM"){
            $message->message="You said:".$exp."\n My guess: 60";
            //this means number is between 40 and 60
            $storefile = fopen($filename, "w") or die ("Could not open");//this is to store WARM/ COLD in the file
            fwrite($storefile, "50\nWARM\n60");
            fclose($storefile);
        }
        else if($exp=="COLD"){
            $message->message="You said:".$exp."\n My guess: 60";
            //number is not between 40 and 60
            $storefile = fopen($filename, "w") or die("Could not open");//this is to store WARM/ COLD in the file
            fwrite($storefile, "50\nCOLD\n60");
            fclose($storefile);
        }
        else{
            $message->message=$exp." is an invalid expression! View rules first by sending [rules].";
        }
    }//first guess over

    else{
        if(trim($thefile[0])=="50"&&trim($thefile[1])=="WARM"){
            if($exp=="WARMER"){//number between 55 and 60
            $message->message="I'm close! My guess: 55";
            $storefile = fopen($filename, "w") or die("Could not open");//increment from 55 now
            fwrite($storefile, "INCREMENT\n55\n60");
            fclose($storefile);
            }
            else if($exp=="COLDER"){
                //let's see if it is between 40 and 50
                $message->message="You said:".$exp." \n My guess: 40";
                $storefile = fopen($filename, "w") or die("Could not open");//check from 40 now
                fwrite($storefile, "CHECK UP 40");
                fclose($storefile);
            }
            else if($exp=="CORRECT"){
                $message->message="Sweet! To play again send [start guessing]. To view the code, send [code for guess]";
            }
        }
        else if(trim($thefile[0])=="CHECK UP 40"){
            if($exp=="WARMER"){
                //number is between 40 and 50
                $message->message="You said:".$exp."\nI think it is between 40 and 50.\nMy guess: 45";
                $storefile = fopen($filename, "w") or die("Could not open");//increment from 55 now
                fwrite($storefile, "BETWEEN\n40\n50\n45\n40");//next time check greater half if next is warmer
                fclose($storefile);
            }
            else if($exp=="COLDER"){
                //number is between 50 and 55
                $message->message="You said:".$exp."\nI think it is between 50 and 55.\n My guess: 51";
                $storefile = fopen($filename, "w") or die("Could not open");//increment till 55 now
                fwrite($storefile, "INCREMENT\n51\n55");
                fclose($storefile);
            }
        }
        //40 and 60 arc over
        //the ^40 and 60 arc starts here
        else if(trim($thefile[0])==50&&trim($thefile[1])=="COLD"){
            if($exp=="WARMER"){
                //number is between 60 and 100
                $message->message="You said:".$exp."\nMy guess: 80";
                $storefile = fopen($filename, "w") or die("Could not open");//increment from 55 now
                fwrite($storefile, "BETWEEN\n60\n100\n80\n60");
                fclose($storefile);
            }
            else if($exp=="COLDER"){
                //number less than 40
                $message->message="You said".$exp."\nMy guess: 40";
                $storefile = fopen($filename, "w") or die("Could not open");//increment from 55 now
                fwrite($storefile, "BETWEEN\n1\n40\n40\n40");
                fclose($storefile);
            }
        }
        if(trim($thefile[0])=="BETWEEN"){
            $lowerb=(int)$thefile[1];
            $upperb=(int)$thefile[2];
            $current=(int)$thefile[3];
            $originalCurrent=$current;
            $previous=(int)$thefile[4];
            if(abs($upperb-$lowerb)>5){
            if($exp=="WARMER"){
                if($current==$previous){
                    $current=($lowerb+$upperb)/2;
                    $current=floor($current);
                    $message->message="You said: ".$exp."\nMy guess: ".$current;
                    $storefile = fopen($filename, "w") or die("Could not open");//like the search thing
                    fwrite($storefile, "BETWEEN\n".$lowerb."\n".$upperb."\n".$current."\n".$originalCurrent);
                    fclose($storefile);
                }
                else if($previous<$current){
                    $lowerb=($lowerb+$current)/2;
                    $lowerb=floor($lowerb);
                    $current=($lowerb+$upperb)/2;
                    $current=floor($current);
                    $message->message="You said".$exp."\nMy guess: ".$current;
                    $storefile = fopen($filename, "w") or die("Could not open");//like the search thing
                    fwrite($storefile, "BETWEEN\n".$lowerb."\n".$upperb."\n".$current."\n".$originalCurrent);
                    fclose($storefile);
                }
                else if($previous>$current){
                    $upperb=($upperb+$current)/2;
                    $upperb=floor($upperb)+1;
                    $current=($lowerb+$upperb)/2;
                    $current=floor($current);
                    $message->message="You said".$exp."\nMy guess: ".$current;
                    $storefile = fopen($filename, "w") or die("Could not open");//like the search thing
                    fwrite($storefile, "BETWEEN\n".$lowerb."\n".$upperb."\n".$current."\n".$originalCurrent);
                    fclose($storefile);
                }
                else{
                    $message->message="Something wrong in algorithm maybe.";
                }
            }
            else if(trim($exp)=="COLDER"){
                if($current==$previous){
                    $current=($lowerb+$upperb)/2;
                    $message->message="You said: ".$exp."\nMy guess: ".$current;
                    $storefile = fopen($filename, "w") or die("Could not open");//like the search thing
                    fwrite($storefile, "BETWEEN\n".$lowerb."\n".$upperb."\n".$current."\n".$originalCurrent);
                    fclose($storefile);
                }
                else if($current<$previous){
                    $lowerb=($lowerb+$current)/2;
                    $lowerb=floor($lowerb);
                    $current=($lowerb+$upperb)/2;
                    $current=floor($current);
                    $message->message="You said".$exp."\nMy guess: ".$current;
                    $storefile = fopen($filename, "w") or die("Could not open");//like the search thing
                    fwrite($storefile, "BETWEEN\n".$lowerb."\n".$upperb."\n".$current."\n".$originalCurrent);
                    fclose($storefile);
                }
                else if($previous<$current){
                    $upperb=($upperb+$current)/2;
                    $upperb=floor($upperb)+1;
                    $current=($lowerb+$upperb)/2;
                    $current=floor($current);
                    $message->message="You said".$exp."\nMy guess: ".$current;
                    $storefile = fopen($filename, "w") or die("Could not open");//like the search thing
                    fwrite($storefile, "BETWEEN\n".$lowerb."\n".$upperb."\n".$current."\n".$originalCurrent);
                    fclose($storefile);
                }
                /*
                if($previous<$current){
                    $upperb=($previous+$upperb)/2;
                    $upperb=floor($upperb);
                    $message="You said:".$exp."\nI think your number is between ".$lowerb." and ".$upperb."\nMy guess: ".$upperb;
                    $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                    fwrite($storefile, "DECREMENT\n".$upperb."\n".$lowerb);
                    fclose($storefile);
                }
                else if($previous>=$current){
                    $lowerb=($previous+$current)/2;
                    $lowerb=floor($lowerb);
                    $message="You said:".$exp."\nI think your number is between ".$lowerb." and ".$upperb."\nMy guess: ".$upperb;
                    $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                    fwrite($storefile, "INCREMENT\n".$lowerb."\n".$upperb);
                    fclose($storefile);
                }*/
            }
            }
            else {//difference too small, let's do increment/decrement
                if(trim($exp)=="COLDER"){
                    if($previous<$current){
                    $upperb=($previous+$upperb)/2;
                    $upperb=floor($upperb);
                    $message="You said:".$exp."\nI think your number is between ".$lowerb." and ".$upperb."\nMy guess: ".$upperb;
                    $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                    fwrite($storefile, "DECREMENT\n".$upperb."\n".$lowerb);
                    fclose($storefile);
                }
                else if($previous>=$current){
                    $lowerb=($previous+$current)/2;
                    $lowerb=floor($lowerb);
                    $message="You said:".$exp."\nI think your number is between ".$lowerb." and ".$upperb."\nMy guess: ".$upperb;
                    $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                    fwrite($storefile, "INCREMENT\n".$lowerb."\n".$upperb);
                    fclose($storefile);
                }
             }
             else if(trim($exp)=="WARMER"){
             if($previous>$current){
                    $upperb=($previous+$upperb)/2;
                    $upperb=floor($upperb);
                    $message="You said:".$exp."\nI think your number is between ".$lowerb." and ".$upperb."\nMy guess: ".$upperb;
                    $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                    fwrite($storefile, "DECREMENT\n".$upperb."\n".$lowerb);
                    fclose($storefile);
                }
                else if($previous<=$current){
                    $lowerb=($previous+$current)/2;
                    $lowerb=floor($lowerb);
                    $message="You said:".$exp."\nI think your number is between ".$lowerb." and ".$upperb."\nMy guess: ".$upperb;
                    $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                    fwrite($storefile, "INCREMENT\n".$lowerb."\n".$upperb);
                    fclose($storefile);
                }
             } 
        }
        }
        else if(trim($thefile[0])=="INCREMENT"){
            $from=(int)$thefile[1];
            $to=(int)$thefile[2];
            $guess=$from+1;
            if($guess<=$to){
                $message->message="You said:".$exp."\nMy guess: ".$guess;
                $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                fwrite($storefile, "INCREMENT\n".$guess."\n".$to);
                fclose($storefile);
            }
            else {
                $message->message="Strange. Are you sure fo your responses? If yes, I'll fix the algorithm. If no, send [start guessing] to start over.";
            }
        }
        else if(trim($thefile[0])=="DECREMENT"){
            $from=(int)$thefile[1];
            $to=(int)$thefile[2];
            $guess=$from-1;
            if($guess>=$to){
                $message->message="You said:".$exp."\nMy guess: ".$guess;
                $storefile = fopen($filename, "w") or die("Could not open");//space very narrow now
                fwrite($storefile, "DECREMENT\n".$guess."\n".$to);
                fclose($storefile);
            }
            else {
                $message->message="Strange. Are you sure your responses are correct? If yes, I'll fix the algorithm. If no, send [start guessing] to start over.";
            }
        }
    }//the other guesses closing
}//closing handle
$rf=new \stdClass();
$rf->replies=array($message);
echo json_encode($rf);
?>
