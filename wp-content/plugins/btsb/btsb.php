<?php
/*
Plugin Name: BTSB plugin
Description: Who cares
Author: Bruno Bionaz
Author URI: http://bnj.xyz
Version: 1.0.0
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! function_exists( 'getWorkList' ) )
      require_once( 'btsb-functions.php' );



add_action('admin_init', 'btsb_scripts_basic' );
add_action('admin_menu', 'btsb_setup_menu');

add_action( 'wp_enqueue_scripts', 'btsb_frontend_scripts' );


function btsb_scripts_basic()
{
    /* Register the scripts for the plugin: */
    wp_register_script( 'admin-script', plugins_url( '/js/admin.js', __FILE__ ) );
    wp_register_style( 'admin-css', plugins_url( '/css/admin.css', __FILE__ ) );


}


function btsb_setup_menu(){
        $page_hook_suffix = add_menu_page( __('BTSB'), __('BTSB Orders'), 'manage_options', 'btsb', 'btsb_init', 'dashicons-images-alt2' );

        add_action('admin_print_scripts-' . $page_hook_suffix, 'btsb_admin_scripts');
}

function btsb_admin_scripts() {
    /* Link our already registered script to a page */
    wp_enqueue_script( 'admin-script' );
    wp_enqueue_style( 'admin-css' );
}

function btsb_frontend_scripts() {
    wp_enqueue_style( 'btsbCSS', plugins_url( '/css/main.css', __FILE__ ) );
    wp_register_script( 'caman', plugins_url( '/js/caman.full.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_script( 'caman' );

}



function btsb_init(){

echo "<div class=\"btsb-plugin\">";

echo "<h1>BTSB Order Management</h1>";



$workList = getWorkList();
// echo"<pre>";
// print_r($workList);
// echo"</pre>";
// echo "<h3>worklist</h3>";


 echo getWorkListAsHTML($workList);

// foreach($workList as $scuolaKey => $scuola){
//
//   $numberImages=0;
//   $numberImagesDone=0;
//
//   foreach($scuola as $classe)
//     foreach ($classe as $img) {
//       $numberImages++;
//       if($img['done']) $numberImagesDone++;
//
//     }
//
//
//   echo "<span>$scuolaKey &rarr; </span> <span>$numberImagesDone</span>/<span>$numberImages</span> <button class=\"startBatch\" data-input=\"$scuolaKey\">Start Batch</button>";
//   echo "<pre>";
//   print_r($scuola);
//
//   echo "</pre>";
//   }


echo '<div class="wrapper-log"><div class="log"></div></div>';

echo "</div>";

}





/* Support functions */
function array2ul($array)
 {
    $depth=array_depth((array)$array);
    if($depth>0) $out="<ul>"; else $out="";
    foreach($array as $key => $elem){
        if($depth==1){
            $out.=" ";
        } /* elseif($depth==2){
            $out.="<li class=\"lev-".$depth."\"><span>$key".array2ul($elem)."</span></li>";
        } else */ {
            if($depth==5) $button="<button>Start batch</button>";
            $out.="<li class=\"lev-".$depth."\"><span>$key:$elem</span>$button".array2ul($elem)."</li>";
        }
    }

    if(array_depth((array)$array)>0) $out.="</ul>"; /*else $out="";*/
    return $out;
}

function array_depth(array $arr)
{
    $depth = function (&$max) {
        return function($line) use (&$max) {
            $max = max(array($max, (strlen($line) - strlen(ltrim($line))) / 4));
        };
    };
    array_map($depth($max), explode(PHP_EOL, print_r($arr, true)));
    return ceil(($max - 1) / 2) + 1;
}



?>
