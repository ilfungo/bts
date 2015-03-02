<?php
/*
Plugin Name: BTS plugin
Description: Who cares
Author: Bruno Bionaz
Author URI: http://bnj.xyz
Version: 1.0.1
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! function_exists( 'getWorkList' ) )
      require_once( 'btsb-functions.php' );

add_action('init', 'bts_localization');

add_action('admin_init', 'btsb_scripts_basic' );
add_action('admin_menu', 'btsb_setup_menu');

add_action( 'wp_enqueue_scripts', 'btsb_frontend_scripts' );


function bts_localization()
{
// Localization
load_plugin_textdomain('bts', false, dirname(plugin_basename(__FILE__)));
}

// Add actions



function btsb_scripts_basic()
{
    /* Register the scripts for the plugin: */
    wp_register_script( 'admin-script', plugins_url( '/js/admin.js', __FILE__ ) );
    wp_register_style( 'admin-css', plugins_url( '/css/admin.css', __FILE__ ) );


}


function btsb_setup_menu(){
        $page_hook_suffix = add_menu_page( __('BTS'), __('BTS Orders'), 'manage_options', 'bts', 'btsb_init', 'dashicons-images-alt2' );

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



function btsb_init(){ ?>

    <div class="btsb-plugin">
    <h1>BTS Order Management</h1>
    <?php
        $workList = getWorkList();
        if(count($workList) >0)
            echo getWorkListAsHTML($workList);
        else
            echo "<table><tr><td>No ununfinished orders found.</td></tr></table>";
    ?>
    <div class="wrapper-log"><div class="log"></div></div>
    </div>


<?php }





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
