<?php

define('_WP_DIR','/home/federico/public_html/btsb.bnj.xyz/');
define('_BTSB_DIR','/home/federico/public_html/btsb.bnj.xyz/wp-content/plugins/btsb/');
define('WP_USE_THEMES', false);
require_once(_WP_DIR.'wp-load.php');

if ( ! function_exists( 'getWorkList' ) )
    require_once( 'btsb-functions.php' );




$worklist=getWorkList();

print_r($worklist);


$fd = fopen(_BTSB_DIR.'batch.started', 'w+');

if(flock($fd, LOCK_EX | LOCK_NB )) {

    ftruncate($fd, 0);      // truncate file


foreach ($worklist[$argv[1]] as $keyClasse => $classe) {

    foreach($classe as $img){
        echo "php "._BTSB_DIR."filter.php {$img['input']} {$img['output']} {$img['filter']}  {$img['vignette']}";
        echo "Starting ".$img['input']."\n";
        if(!is_WorkListItemDone($img['output'])){
            echo "doing\n";
            exec("php "._BTSB_DIR."filter.php {$img['input']} {$img['output']} {$img['filter']} {$img['vignette']}" );
        } else {
            //echo "done yet\n";
        }
        fwrite($fd, $argv[1].' '. $keyClasse . ' ' .$img['input']."\n");
        //echo "Done ".$img['input']."\n";
    }
}

echo "All done!";

    sleep(10);
    fflush($fd);
    flock($fd, LOCK_UN);
} else {
    //echo 'already running';
}

fclose($fd);






?>
