<?php



if ( ! function_exists( 'getWorkList' ) )
    require_once( 'btsb-functions.php' );

require_once(_WP_DIR.'wp-load.php');

if(isset( $_REQUEST['arg'] ) )
    
    $arg = $_REQUEST['arg'];

elseif(isset($argv[1]))

    $arg = $argv[1];

else

    $arg = trim(file_get_contents(_SCHEDULE_FILE));

if(empty($arg)) die("Nothing to do");



define( '_LOCK_FILE', _BTSB_DIR.'batch'."-".$arg.".lock" );

//echo  _SCHEDULE_FILE;


$worklist=getWorkList();




foreach ($worklist as $scuolaKey => $scuola) {
    foreach ($scuola as $classeKey => $classe) {

            //print_r($img);
            if(array_key_exists($arg, $classe)){
                $singleBatch=$worklist[$scuolaKey][$classeKey][$arg];
                continue;
            }

    }
}




//$fd = fopen(_BTSB_DIR.'log/batch.lock', 'w+');
//echo "Log file: ". _BTSB_DIR.'log/batch.'.$arg.'.log' ."\n";
$fdLog = fopen(_BTSB_DIR.'log/batch.'.$arg.'.log', 'a');

//$fdStatus = fopen(_BTSB_DIR.'batch.'.$arg.'.running', 'a');

if( isLocked() ) die( "Already running.\n" );
//if(flock($fd, LOCK_EX | LOCK_NB )) {

    //ftruncate($fd, 0);      // truncate file


if(!empty($worklist[$arg])) {
    foreach ($worklist[$arg] as $keyClasse => $classe) {

        foreach($classe as $keyImg => $img){

            $logStr= $keyClasse . ' ' . $img['title'] . ' ' .$img['filter'] . ' ' .$img['vignette'];

            //echo "php "._BTSB_DIR."filter.php {$img['input']} {$img['output']} {$img['filter']} {$img['vignette']}\n";
            //echo $img['type']."\n";
            if( !(is_WorkListItemDone($img['output'])) && !($img['type']=='annuario')){

                $a=$img['type']=='annuario';


                if(file_exists($img['input'])){

                    fwrite($fdLog, date('c') .' '. $logStr . ' Filtering started' . "\n");
                
                    exec("php "._BTSB_DIR."filter.php " . escapeshellcmd( $img['input'] . " " . $img['output'] . " " . $img['filter'] . " " . $img['vignette'] ) . "" );
                    fwrite($fdLog, date('c') .' '. $logStr . ' Filtering done' . "\n");

                } else { fwrite($fdLog, date('c') .' '. $logStr . ' Input file not found' . "\n"); }








            } else {
                fwrite($fdLog, date('c') .' '. $logStr . ' Skipping' . "\n");
            }

        }
    }
} elseif(!empty($singleBatch)){

    $img = $singleBatch;

            print_r($img);
            
            if(!is_WorkListItemDone($img['output']) && $img['type']!='annuario'){
                echo "doing img\n";
                
                echo "php "._BTSB_DIR."filter.php " . escapeshellcmd( $img['input'] . " " . $img['output'] . " " . $img['filter'] . " " . $img['vignette'] ) . " &";
                //exec("php "._BTSB_DIR."filter.php {$img['input']} {$img['output']} {$img['filter']} {$img['vignette']}" );
                exec("php "._BTSB_DIR."filter.php " . escapeshellcmd( $img['input'] . " " . $img['output'] . " " . $img['filter'] . " " . $img['vignette'] ) . " &" );


                fwrite($fdLog, date('c') .' '. $keyClasse . ' ' .$img['filter'].' '.$timeElapsed."\n");

            } else {
                fwrite($fdLog, date('c') .' '. $logStr . ' Skipping' . "\n");
            }

}

//  echo "All done!";



    sleep(1);
    unlink( _LOCK_FILE );


//fclose($fd);





?>
