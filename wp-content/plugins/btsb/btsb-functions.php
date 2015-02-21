<?php

define('_WP_DIR','/home/federico/public_html/btsb.bnj.xyz/');
define('_BTSB_DIR','/home/federico/public_html/btsb.bnj.xyz/wp-content/plugins/btsb/');
define('_ORDERS_DIR','/home/federico/ordini/');
define( '_SCHEDULE_FILE', _BTSB_DIR."batch.schedule" );
define('WP_USE_THEMES', false);

date_default_timezone_set('Europe/Rome');


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
    //echo "<pre>";
    //print_r($item);
    //echo "</pre>";


    $product= new WC_Product($item['product_id']);

    $type = $product->post->post_title;

    $parent_id = wp_get_post_parent_id( $item['product_id'] );


    if(  $parent_id ){
      $product= new WC_Product($parent_id);

    }


    if($parent_id) $cat=$parent_id; else $cat=$product_id;
    $term_list = wp_get_post_terms($cat,'product_cat');
    $classe = $term_list[0];
    $scuola= get_term( $classe->parent, 'product_cat' ) ;

    if(empty($item['Filters']))  $item['Filters']='originale'; else $item['Filters']=strtolower($item['Filters']);

    if(empty($item['Vignette']))  $item['Vignette']=''; else $item['Vignette']='vignette';

    if(empty($item['qty']))  $item['qty']=1;
    $item['slug']=sanitize_file_name($item['name']);



   //Metto tutto in una hashmap (per visualizzare l'albero delle lavorazioni)
    $thumb = wp_get_attachment_image_src( $product->get_image_id( ) );
    $pathInput=pathinfo(  parse_url($thumb[0])['path'] );

    $workListItemTitle=$product->get_title();
    $workListItemBaseDir = _ORDERS_DIR;
    $workListItemFilter = $item['Filters'];
    $workListItemVignette = $item['Vignette']?1:0   ;
    $workListItemInput =str_replace('/wp-content/uploads', ABSPATH.'wp-content/uploads/woocommerce_uploads', $pathInput['dirname']).'/downloadable_'.$pathInput['basename'];
    $workListItemHashName= $pathInput['filename'].'_'.$item['Filters'].'_'.$item['Vignette'];
    $workListItemOutput = $workListItemBaseDir.$scuola->slug.'/'.$classe->slug.'/'.$pathInput['filename'].'_'.$item['Filters'].'_'.$item['Vignette'].'_'.$item['qty'].'.jpg';
    $workListItemDone = is_WorkListItemDone($workListItemOutput);

    $workList[$scuola->slug][$classe->slug][$workListItemHashName]= array(   'title'=>$workListItemTitle,
                                                        'order_id'=>substr($workList[$scuola->slug][$classe->slug][$workListItemHashName]['order_id']  . $order->id, 0) .", " ,
                                                        'type'=>$type,
                                                        'input'=>$workListItemInput,
                                                        'output'=>$workListItemOutput,
                                                        'filter'=>$workListItemFilter,
                                                        'vignette'=>$workListItemVignette,
                                                        'qty'=>$workList[$scuola->slug][$classe->slug][$workListItemHashName]['qty']+$item['qty'],
                                                        'done'=>$workListItemDone);
  }

}

return $workList;
}




function is_WorkListItemDone($i){
  return (file_exists($i) && (exif_imagetype($i) == IMAGETYPE_JPEG) && filesize($i)>1024);
}


function getWorkListAsHTML($worklist){
   //var_dump($worklist);
  if(isset($_GET['scuola'])){

    $worklistScuola[$_GET['scuola']]=$worklist[$_GET['scuola']];

    $str='<ul class="wl">';
    foreach ($worklistScuola as $scuolaKey => $scuola) {

      $count=getWorklistItemCount($scuola);

      $str.='<li class="scuola '.$scuolaKey .'">';
      //$str.='<h2>'.$scuolaKey.'</h2> ';
      //$str.="<button class=\"startBatch\" data-input=\"$scuolaKey\">Start School Batch</button>";
      //$str.='<span>'.$count['numberImagesDone'].'/'.$count['numberImages'].'</span> ';
      $str.='<table class="table">';
      $str.= getHTMLTableHeader($scuola, $scuolaKey );
      $str.='</table>';

      $str.="<ul>";
        foreach ($scuola as $classeKey => $classe) {

          $count=getWorklistItemCount($scuola,$classeKey);


          $str.='<li class="classe">';

          $str.='<table class="label"><tr>';
          $str.='<td><h3>'.$classeKey.'</h3></td>';
          //$str.="<button class=\"startBatch\" data-input=\"$scuolaKey\">Start Class Batch</button>";
          $str.='<td>'.$count['numberImagesDone'].'/'.$count['numberImages'].'</td> ';
          $str.='</tr></table>';

          $str.="<table>";
            $str.="<tr><th>Order</th><th>Type</th><th>Title</th><th>Filter</th><th>Vignette</th><th>Quantity</th><th>Done</th></tr>";
          foreach ($classe as $imgKey => $img) {
            $done=is_WorkListItemDone($img['output']);
            $doneClass= $done ? 'done' : 'not-done';


            $vignetteStr = $img['vignette'] ? '<span>✓</span>' : '';

            $str.='<tr class="'.$doneClass.' img">';

            /*ORDER CELL*/
            $str.='<td class="order">'.substr($img['order_id'],0, -2).'</td>';

            /*TYPE CELL*/
            $str.='<td class="type">'.$img['type'].'</td>';

            /*TITLE CELL*/
            if(!file_exists($img['input'])) { $titleStr='<s>'.$img['title'].'</s>' ; $altStr="title=\"File doesn't exists!\""; } else { $titleStr=$img['title']; }
            $str.='<td class="title" '.$altStr.'>'.$titleStr.'</td>';
            /*ORDER CELL*/
            $str.='<td class="filter-name">'.$img['filter'].'</td>';
            /*ORDER CELL*/
            $str.='<td class="vignette">'.$vignetteStr.'</td>';
            /*ORDER CELL*/
            $str.='<td class="qty">'.$img['qty'].'</td>';
            if(!$done && $img['type']!='annuario') $buttonStr = "<button class=\"startBatch\" data-input=\"$imgKey\">Start Single</button>"; else $buttonStr = "Yes!";
            $str.='<td class="done">'.$buttonStr.'</td>';


            $str.="</tr>";
          }
          $str.='</table>';



          $str.="</li>";
        }
      $str.='</ul>';

      $str.="</li>";
    }
    $str.='</ul>';

  } else {

    //$str='<ul class="wl">';
    $str.='<table class="table">';
    foreach ($worklist as $scuolaKey => $scuola) {

      $str.= getHTMLTableHeader($scuola, $scuolaKey );

    }
    $str.='</table>';
  }



  return $str;
}

function getWorklistItemCount($scuola, $classeKey=false){

  $numberImages=0;
  $numberImagesDone=0;

  if($classeKey) {
    foreach ($scuola[$classeKey] as $img) {
      if($img['type'] != 'annuario') {
        $numberImages++;
        if($img['done']) $numberImagesDone++;
      }
    }

  } else

  foreach($scuola as $classe)
    foreach ($classe as $img) {
      if($img['type'] != 'annuario') {
        $numberImages++;
        if($img['done']) $numberImagesDone++;
      }
    }
  return array("numberImages"=>$numberImages, "numberImagesDone"=> $numberImagesDone );
}


function isLocked()
{
    # If lock file exists, check if stale.  If exists and is not stale, return TRUE
    # Else, create lock file and return FALSE.
    echo _LOCK_FILE."\n";
    if( file_exists( _LOCK_FILE ) )
    {   echo "Lock file exists\n";
        # check if it's stale
        $lockingPID = trim( file_get_contents( _LOCK_FILE ) );

       # Get all active PIDs.
        $pids = explode( "\n", trim( `ps -e | awk '{print $1}'` ) );

        # If PID is still active, return true
        if( in_array( $lockingPID, $pids ) )  return true;

        # Lock-file is stale, so kill it.  Then move on to re-creating it.
        echo "Removing stale lock file.\n";
        unlink( _LOCK_FILE );
    }
    echo "Lock file doesn't exists\n";
    echo getmypid()."\n";
    file_put_contents( _LOCK_FILE, getmypid() . "\n" );
    return false;

}


function getHTMLTableHeader($scuola, $scuolaKey){

      $count=getWorklistItemCount($scuola);
      $doneClass = ($count['numberImages'] == $count['numberImagesDone']) ? 'done ':'not-done ';

      $str.='<tr class="scuola '.$doneClass.$scuolaKey .'">';

      $str.='<td><h2><a href="'.admin_url( "admin.php?page=".$_GET["page"] ).'&scuola='.$scuolaKey.'">'.$scuolaKey.'</a></td></h2> ';
      $str.="<td><button class=\"startBatch\" data-input=\"$scuolaKey\">Schedule School Batch</button></td>";
      $str.="<td><button class=\"showLog\" data-input=\"$scuolaKey\">Show School Log</button></td>";
      $str.='<td><span>'.$count['numberImagesDone'].'/'.$count['numberImages'].'</span></td>';

      $lockFile=_BTSB_DIR.'batch-'.$scuolaKey.'.lock';
      $started= filemtime($lockFile);
      $now=time();
      $eta= ($now - $started) / $count['numberImagesDone'];
      $eta = ($count['numberImages']-$count['numberImagesDone'])*$eta;
      $eta += $started;
      $scheduled=file_get_contents(_SCHEDULE_FILE);
      if(file_exists($lockFile)){
        $runningStr = 'Started at '.date ("H:i:s.", $started).'<br>ETA '.date ("H:i:s.", $eta).'<br>PID:'.file_get_contents($lockFile)."<br>";
      } else { $runningStr="Stoppped"; }

      if( trim($scheduled) == $scuolaKey ) {
        $scheduledStr = 'Scheduled'.'<br>';
      } else { $scheduledStr =''; }
      $str.='<td><span>'.  $scheduledStr.$runningStr . ' ' .'</span></td>';
      $str.="</tr>";

      return $str;


}



?>
