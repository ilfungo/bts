<?php
get_header(); ?>
    <div class="page-seperator"></div>
    <div class="container">
        <div class="row">
            <div class="qua_page_heading">
                <h1><?php the_title(); ?></h1>
                <div class="qua-separator"></div>
            </div>
        </div>
    </div>
<div class="container">
    <div class="row qua_blog_wrapper">
<div class="<?php if( is_active_sidebar('sidebar-primary')) { echo "col-md-8"; } else { echo "col-md-12"; } ?>">
<?php
    //script classi scuola

    // Cerco tutti gli ordini

$args = array(
    'post_type' => 'shop_order',
    'post_status' => array( 'wc-on-hold', 'wc-processing' ),
    'posts_per_page' => '-1'
);

$my_query = new WP_Query($args);
$order_summary = $my_query->posts;

if ( !empty( $order_summary ) ) : $totals = 0;

    $user_id = get_current_user_id();


    foreach ( $order_summary as $order_post ) :
        //echo "<br>entro qui";
        //questi sono gli ordini
        $order = new WC_Order( );
        $order->populate($order_post);
        $valid_items = $order->get_items();//WCV_Queries::get_products_for_order( $order->id );
        $itemsForOrder[$order->id]=$valid_items;
        $classe = '';
        foreach ($valid_items as $valid_item) {

            if($valid_item['Sold by'] != get_user_meta( $user_id, 'nickname', true) ){
                continue 2; //
            }
            $p_id=$valid_item['product_id'];
            $valid_item=new WC_Product($p_id);
            $cat=get_the_terms( $p_id, 'product_cat' );
            $actual_class="";
            if(empty($cat)){
                $cat=get_the_terms( $valid_item->get_parent(), 'product_cat' );
                $classe=$cat[0];

            } else {
                $classe=$cat;
                //$actual_class=$cat;
            }

            //if(is_array($actual_class)){print_r($actual_class);}

            $scuola = get_term_by( 'id', $classe->parent, 'product_cat');
            $dir =  ABSPATH.'wp-content/uploads/report/'.$scuola->slug;
            $site_url = get_site_url();
            $report_url = $site_url.'/wp-content/uploads/report/'.$scuola->slug;

            //script folder
            if ( !file_exists( $dir ) ) {
                @mkdir( $dir, 0755, true );
            }

        }


        //Preparo la hasmap che userò in visualizzazione
        if(isset($_GET['c']) && $_GET['c']>0){
            if($_GET['c'] == $classe->slug)
                $allOrders[$classe->slug][$order->get_user()->data->display_name][$order->get_order_number( )]=array('total'=>$order->get_total() , 'date'=>$order->order_date, 'status'=>$order->get_status() );
        } else {
            $allOrders[$classe->slug][$order->get_user()->data->display_name][$order->get_order_number( )]=array('total'=>$order->get_total() , 'date'=>$order->order_date, 'status'=>$order->get_status() );
        }

    endforeach;

    $className = "";
    foreach($allOrders as $orderedClassKey => $orderedClass ):
        $totals =0;
        $idObj = get_term_by('slug',$orderedClassKey, 'product_cat');
        $className = $idObj->name;
        $classSlug = $idObj->slug;
        $csvTableClass = "\r\n\r\n".$idObj->name. "\r\n";
        $csvTableClass .= "Studente" . ';' . "ID ordine" . ";" . "Nome foto" . ";" . "Quantita" . ";" ."Costo singolo" . ";" ."Totale ordine" . "\r\n" ;
        foreach($orderedClass as $orderedCustomerKey => $orderedCustomer ):
            foreach($orderedCustomer as $orderedOrderKey => $orderedOrder ):
                $totals+=$orderedOrder['total'];
                foreach ($itemsForOrder[substr($orderedOrderKey,1)] as $key => $item):
                    if(empty ($item['Filters'])) $item['Filters']='Originale';
                    if(empty ($item['Vignette'])) $item['Vignette']='No Vignette';
                    $wrongText = array("foto di classe","foto focus","annuario","&rarr;","_new");
                    $goodText   = array("","","","","annuario_new");
                    $name = str_replace($wrongText,$goodText,$item['name']);
                    $fmt = numfmt_create( 'de_DE', NumberFormatter::CURRENCY );
                    //echo $orderedOrderKey;
                    /*if($orderedOrderKey==-1){// non si avvera mai cosa vuol dire????
                        //echo "orderedOrderKey -1 ".$orderedOrderKey."<br>";
                        if (strpos($csvTableClass, $orderedOrderKey) == true) {
                            $csvTableClass .= ';' . ';' . $name . ';' . $item['qty'] . ';' . $item['line_total'] . ' euro' . ';' .$orderedOrder['total'].';'. "\r\n";
                        }
                        else {
                            $csvTableClass .= $orderedCustomerKey . ';' . $orderedOrderKey . ';' . $name . ';' . $item['qty'] . ';' . $item['line_total'] . ' euro' . ';' . "\r\n";
                        }
                    }
                    else {*/
                        //echo "orderedOrderKey non è  -1 ".$orderedOrderKey."<br>";
                        if (strpos($csvTableClass, $orderedOrderKey) == true) {
                            $csvTableClass .= ';' . ';' . $name . ';' . $item['qty'] . ';' . $item['line_total'] . ' euro' . ';;'. "\r\n";
                        }/*
                        elseif(strpos($csvTableClass, $orderedOrderKey) == true){
                            $csvTableClass .= $orderedCustomerKey . ';' . $orderedOrderKey . ';' . $name . ';' . $item['qty'] . ';' . $item['line_total'] . ' euro' . ';' . "\r\n" ."\r\n";
                        }*/
                        else {
                            $csvTableClass .= $orderedCustomerKey . ';' . $orderedOrderKey . ';' . $name . ';' . $item['qty'] . ';' . $item['line_total'] . ' euro' . ';' .$orderedOrder['total'].' euro;' . "\r\n";
                        }
                    //}
                    ?>
                    <?php //comments_template(); ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <!-- aggiungo il totale per ordine -->
        <?php endforeach; ?>
        <?php
        $csvTableClass .= "\r\n".'Totale :;' . ';' . ';' . ';' . ';'. $totals . ' euro' ."\r\n"; ?>
        <?php
        $csvTable .= $csvTableClass;
        $report_name = $dir."/report_".$scuola->slug."_".$className.".csv";
        $file_url = $report_url ."/report_".$scuola->slug."_".$className.".csv";
        chmod($report_name, 0775);
        $csvTableClass = mb_convert_encoding($csvTableClass, "ISO-8859-2");//"ISO-8859-2"  UTF-8);
        //echo $csvTableClass = iconv('', 'UTF-8', $csvTableClass);
        if(!write_txt_file($csvTableClass,$report_name)){
            echo "Qualche problema nella scrittura del report per favore contattare l'amministratore del sito!<br>";
        }else{ ?>
            scaricare il report della <a href="<?php echo $file_url;?>"><b>classe <?php echo $className?></b></a>
            //
            vai alla classe per stampare le foto <b><a href="/?product_cat=<?php echo $classSlug;?>" target="_blank">classe <?php echo $className ?></a></b>
            <br>
        <?php }
        ?>
    <?php endforeach; ?>

                <?php
                $report_name = $dir."/report_".$scuola->slug.".csv";
                $file_url = $report_url ."/report_".$scuola->slug.".csv";
                chmod($report_name, 0775);
                if(!write_txt_file($csvTable,$report_name)){
                    echo "Qualche problema nella scrittura del report per favore contattare l'amministratore del sito!<br>";
                }else{ ?>
                    <a href="<?php echo $file_url;?>">Scaricare il report <b>per tutte le classi della scuola</b></a><br>
                <?php }
                ?>
            </div>
            <?php get_sidebar(); ?>
        </div>
    </div>
<?php endif; ?>
<?php get_footer(); ?>