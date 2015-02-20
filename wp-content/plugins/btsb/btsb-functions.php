<?php

//define('WP_USE_THEMES', false);
//require_once('../../../wp-load.php');

function getWorkList(){

  $orderedOrders  = null;
  $workList = null;

  $args = array(
    'post_type' => 'shop_order',
    'post_status' => 'publish',
    'posts_per_page' => '-1'
    );
  $my_query = new WP_Query($args);

  $customer_orders = $my_query->posts;






  foreach ($customer_orders as $customer_order) {

   $order = new WC_Order();

   $order->populate($customer_order);
   $items=$order->get_items();
   $user=$order->get_user();
   $status=$order->get_status();

   if($status=='completed') continue;

   foreach ($items as $item) {


    $product= new WC_Product($item['product_id']);

    $parent_id = wp_get_post_parent_id( $item['product_id'] );

    if(  $parent_id ){
      $parent= new WC_Product($parent_id);

    }

    if($parent_id) $cat=$parent_id; else $cat=$product_id;
    $term_list = wp_get_post_terms($cat,'product_cat');
    $classe = $term_list[0];
    $scuola= get_term( $classe->parent, 'product_cat' ) ;

    if(empty($item['Filters']))  $item['Filters']='originale'; else $item['Filters']=strtolower($item['Filters']);

    if(empty($item['Vignette']))  $item['Vignette']=0; else $item['Vignette']=1;

    if(empty($item['qty']))  $item['qty']=1;
    $item['slug']=sanitize_file_name($item['name']);







   //Metto tutto in una hashmap (per visualizzare l'albero delle lavorazioni)
   //$orderedOrders[$scuola->slug?$scuola->slug:'unknow'][$classe->slug?$classe->slug:'unknow'][$item['name']][$item['Filters']][$item['Vignette']]+=$item['qty'];

  //seconda hashmap (per la worklist)
    $thumb = wp_get_attachment_image_src( $product->get_image_id( ) );
    $pathInput=pathinfo(  parse_url($thumb[0])['path'] );




    $workListItemBaseDir = "/home/federico/ordini/";
    $workListItemFilter = $item['Filters'];
    $workListItemVignette = $item['Vignette'];
    $workListItemInput =str_replace('/wp-content/uploads', ABSPATH.'wp-content/uploads/woocommerce_uploads', $pathInput['dirname']).'/downloadable_'.$pathInput['basename'];
    $workListItemOutput = $workListItemBaseDir.$scuola->slug.'/'.$classe->slug.'/'.$pathInput['filename'].'_'.$item['Filters'].'_'.$item['Vignette'].'_'.$item['qty'].'.jpg';
    $workListItemDone = is_WorkListItemDone($workListItemOutput);
    $workList[$scuola->slug][$classe->slug][]= array('input'=>$workListItemInput, 'output'=>$workListItemOutput,'filter'=>$workListItemFilter,'vignette'=>$workListItemVignette, 'done'=>$workListItemDone);
  }

}

return $workList;
}

function is_WorkListItemDone($i){
  return (file_exists($i) && filesize($i)>1024);
}

?>
