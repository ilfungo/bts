<?php

/* ESECUZIONE COMANDI, DA LASCIARE PRIMA DI TUTTO */


/* Cambio stato dell'ordine */
if( isset($_GET['wc_pv_mark_ready']) ){




	$orders=explode(' ', trim( $_GET['wc_pv_mark_ready'] ));



	if(count($orders) <= 1 ) {


		$order=new WC_Order($orders[0]);

		if($order->get_status() != 'processing'){
			$order->update_status('processing',__('Lo stato dell\'ordine è stato cambiato dalla Vendor Dashboard.','bts'));
		}
		else{
			$order->update_status('on-hold',__('Lo stato dell\'ordine è stato cambiato dalla Vendor Dashboard.','bts'));
		}


	} else {
		foreach($orders as $o){

			$order=new WC_Order($o);
			$order->update_status('processing',__('Lo stato dell\'ordine è stato cambiato dalla Vendor Dashboard.','bts'));

		}

	}
}



/* Cambio quantità */
if( isset($_GET['wc_pv_change_qty']) ){


	//TODO SECURITY




	$args = explode(' ', $_GET['wc_pv_change_qty']);

	if( (isset($args[3]) && intval($args[3])>=0 && intval($args[3])<=80 )  ):

		$product=new WC_Product($args[2]);

		if(!empty($product)) :
			$price=$product->price;
			var_dump(intval($args[3]*$price));
				echo "<br>";

			global $wpdb;

			/* Update _qty */
			$wpdb->update(
				'wp_woocommerce_order_itemmeta',
				array(
					'meta_value' => $args[3]	// string
					),
				array( 'order_item_id' => $args[1],
					'meta_key' => '_qty' )
				) ;

			/* Update _line_total */
			$wpdb->update(
				'wp_woocommerce_order_itemmeta',
				array(
					'meta_value' => intval($args[3])*$price	// string
					),
				array( 'order_item_id' => $args[1],
					'meta_key' => '_line_total' )
				) ;

			/* Update _line_subtotal */
			$wpdb->update(
				'wp_woocommerce_order_itemmeta',
				array(
					'meta_value' => intval($args[3])*$price	// string
					),
				array( 'order_item_id' => $args[1],
					'meta_key' => '_line_subtotal' )

				);

			/* Ricalcolo i totali per l'ordine  */
			$p=get_post($args[0]);
			$order=new WC_Order();
			$order->populate($p);
			var_dump($order);
			$order->calculate_totals();
			var_dump($order);
		endif;

	endif;

}

?>



<script type="text/javascript">

	function downloadFile(fileName, urlData) {
		var aLink = document.createElement('a');
		var evt = document.createEvent("HTMLEvents");
		evt.initEvent("click");
		aLink.download = fileName;
		aLink.href = urlData;
		aLink.dispatchEvent(evt);
	}



	<?php
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

				//$classe=$valid_item->get_categories(' ','','');
				$cat=get_the_terms( $p_id, 'product_cat' );

				if(empty($cat)){

					$cat=get_the_terms( $valid_item->get_parent(), 'product_cat' );
					$classe=$cat[0];

				} else {
					$classe=$cat;
				}
			}
		//$valid = array();
		//$items = $order->get_items();
		//foreach ($items as $key => $value) {
		//	if ( in_array($value['variation_id'], $valid_items) || in_array($value['product_id'], $valid_items)) {
		//		$valid[] = $value;
		//	}
		//}
		//var_dump(get_post_meta( $order->id ));
		//$shippers = (array) get_post_meta( $order->id, 'wc_pv_shipped', true );
		//$shipped = in_array($user_id, $shippers);
			//Preparo la hasmap che userò in visualizzazione
		if(isset($_GET['c']) && $_GET['c']>0){
			if($_GET['c'] == $classe->slug)
			$allOrders[$classe->slug][$order->get_user()->data->display_name][$order->get_order_number( )]=array('total'=>$order->get_total() , 'date'=>$order->order_date, 'status'=>$order->get_status() );
		} else {
			$allOrders[$classe->slug][$order->get_user()->data->display_name][$order->get_order_number( )]=array('total'=>$order->get_total() , 'date'=>$order->order_date, 'status'=>$order->get_status() );
		}

	endforeach;
	foreach($allOrders as $orderedClassKey => $orderedClass ){
		$idObj = get_term_by('slug',$orderedClassKey, 'product_cat');
	}
	$className = $idObj-> name;
	?>

	var csvTable = "<?php echo 'Classe : ' . $className . "\\n".
		         'Cliente;'. 'ID ordine;' . 'Totale;' . 'Nome file;' . 'Quantita;' . 'Costo;' . "\\n"
   				 ;
		        ?>";
	<?php

	//prendo l'id ordine
	$idOrdine = '';
	foreach($allOrders as $orderedClassKey => $orderedClass ):
		$idObj = get_term_by('slug',$orderedClassKey, 'product_cat');
		foreach($orderedClass as $orderedCustomerKey => $orderedCustomer ):
			foreach($orderedCustomer as $orderedOrderKey => $orderedOrder ):
				foreach ($itemsForOrder[substr($orderedOrderKey,1)] as $key => $item):
					if(empty ($item['Filters'])) $item['Filters']='Originale';
					if(empty ($item['Vignette'])) $item['Vignette']='No Vignette';

				$wrongText = array("foto di classe","foto focus","annuario","&rarr;","_new");
				$goodText   = array("","","","","annuario_new");
				$name = str_replace($wrongText,$goodText,$item['name']);
				$fmt = numfmt_create( 'de_DE', NumberFormatter::CURRENCY );

		?>
	    if(csvTable.indexOf("<?php echo $orderedOrderKey ?>") == -1){
			csvTable += "<?php echo $orderedCustomerKey .';' . $orderedOrderKey .';'. $orderedOrder['total'].  ' euro ' . ';' . $name . ';' . $item['qty'] .';'. $fmt->formatCurrency($item['line_total'],"EUR") . ';'. "\\n";  ?>";
		}else{
			csvTable += "<?php echo $orderedCustomerKey . ';' . $orderedOrderKey . ';' . ' ' . ';' . $name . ';' . $item['qty'] .';'. $item['line_total'] . ' euro' . ';'. "\\n";  ?>";
		}
		//csvTable = csvTable.trim();
	<?php
				endforeach;
			endforeach;
		endforeach;
	endforeach;
	endif; ?>

	jQuery(function () {
	jQuery('a.view-items').on('click', function (e) {
		e.preventDefault();
		var id = jQuery(this).attr('id');

		if ( jQuery(this).text() == "<?php _e('Nascondi dettagli', 'bts'); ?>" ) {
			jQuery(this).text("<?php _e('Vedi dettagli', 'bts'); ?>");
		} else {
			jQuery(this).text("<?php _e('Vedi dettagli', 'bts'); ?>");
		}

		jQuery("#view-items-" + id).fadeToggle();
	});



	jQuery('.table').on('change', '.details input.qty', function(){

		var $val=jQuery(this).val();
		console.log($val);
		var $a =jQuery(this).closest('tr').find('a');

		var $attr= $a.attr('href');


		$a.attr('href', $attr.substring(0,$attr.lastIndexOf("+")+1) +$val) ;

	});
	jQuery('.details input.qty').trigger('change'); //Fix per salvataggio campo vuoto se non modificato
	//rimuovo href da bottone
	jQuery('.export').removeAttr('href');
});
</script>

<h2><a href="<?php the_permalink(); ?>"><?php _e( 'Lista Ordini', 'bts' ); ?></a></h2>



<?php global $woocommerce; ?>





<?php if ( function_exists( 'wc_print_notices' ) ) { wc_print_notices(); } ?>


<?php

/* SEZIONE VISUALIZZAZIONE */

$page_link=get_the_permalink();

/* Lista ordini */




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

			//$classe=$valid_item->get_categories(' ','','');
			$cat=get_the_terms( $p_id, 'product_cat' );

			if(empty($cat)){

				$cat=get_the_terms( $valid_item->get_parent(), 'product_cat' );
				$classe=$cat[0];

			} else {
				$classe=$cat;
			}
		}
		//$valid = array();
		//$items = $order->get_items();
		//foreach ($items as $key => $value) {
		//	if ( in_array($value['variation_id'], $valid_items) || in_array($value['product_id'], $valid_items)) {
		//		$valid[] = $value;
		//	}
		//}
		//var_dump(get_post_meta( $order->id ));
		//$shippers = (array) get_post_meta( $order->id, 'wc_pv_shipped', true );
		//$shipped = in_array($user_id, $shippers);
			//Preparo la hasmap che userò in visualizzazione
		if(isset($_GET['c']) && $_GET['c']>0){
			if($_GET['c'] == $classe->slug){
				$allOrders[$classe->slug][$order->get_user()->data->display_name][$order->get_order_number( )]=array('total'=>$order->get_total() , 'date'=>$order->order_date, 'status'=>$order->get_status() );
			}
		} else {
			$allOrders[$classe->slug][$order->get_user()->data->display_name][$order->get_order_number( )]=array('total'=>$order->get_total() , 'date'=>$order->order_date, 'status'=>$order->get_status() );
		}

	endforeach;

			ksort($allOrders);

			//script csv Simone

			//echo "<pre>"; var_dump($allOrders); echo "</pre>";

			foreach($allOrders as $orderedClassKey => $orderedClass ) :
                    if(isset($_GET['c'])&& $_GET['c']>0){?>
			<h2><a href="<?php echo $page_link; ?>&amp;c=<?php echo $orderedClassKey; ?>"><?php $idObj = get_term_by('slug',$orderedClassKey, 'product_cat');
 				 echo $idObj->name; ?></a></h2>
                    <?php }?>

			<table class="table table-condensed table-vendor-sales-report">

				<?php if(isset($_GET['c'])) : ?>
				<thead>
					<tr>
						<th class="user-header"><?php _e( 'Acquirente', 'bts' ); ?></th>
						<th class="product-header"><?php _e( 'Ordine', 'bts' ); ?></th>
						<th class="commission-header"><?php _e( 'Totale', 'bts' ) ?></th>
						<th class="rate-header"><?php _e( 'Data', 'bts' ) ?></th>
						<th class="status-header"><?php _e( 'Stato', 'bts' ) ?></th>
						<th class="rate-header"><?php _e( 'Azioni', 'bts' ) ?></th>
					</tr>
				</thead>
				<?php endif; ?>
					<tbody>


						<?php


						$totals =0;
						$processing=0;
						$ordercount=0;

						$strOrderList='';

						foreach($orderedClass as $orderedCustomerKey => $orderedCustomer ) :



							ksort($orderedClass);
							foreach($orderedCustomer as $orderedOrderKey => $orderedOrder ) :


							$strOrderList.=substr($orderedOrderKey,1)."+";
							$totals+=$orderedOrder['total'];
							$processing+= ($orderedOrder['status']=='processing');
							$ordercount++;
							if($processing == $ordercount) $strOrderReady ='done'; else $strOrderReady ='not-done';
						?>
						<?php if(isset($_GET['c'])) : ?>
						<tr>
							<td><?php echo $orderedCustomerKey; ?></td>
							<td><?php echo $orderedOrderKey ?></td>
							<!-- <td><?php $sum = WCV_Queries::sum_for_orders( array( $order->id ), array('vendor_id'=>get_current_user_id()) ); $total = $sum[0]->line_total; $totals += $total; echo woocommerce_price( $total ); ?></td> -->
							<td><?php echo woocommerce_price($orderedOrder['total']); ?></td>
							<td><?php echo $orderedOrder['date']; ?></td>
							<td><?php if($orderedOrder['status']=='processing') echo 'Pagato'; elseif($orderedOrder['status']=='on-hold') echo 'Non pagato'; else echo $orderedOrder['status'];  ?></td>
							<!--<td><a href="#" class="view-items" id="<?php echo $order->id; ?>"><?php _e('View details', 'bts'); ?></a> | <a href="?wc_pv_mark_shipped=<?php echo $order->id; ?>" class="mark-shipped"><?php echo $shipped ? __('Unmark shipped', 'bts') : __('Mark shipped', 'bts'); ?></a> <?php if ( $providers ) : ?>  <a href="#" class="view-order-tracking" id="<?php echo $order->id; ?>"><?php _e( 'Tracking', 'bts' ); ?></a><?php endif; ?></td> -->
							<td>
								<a href="#" class="view-items" id="<?php echo substr($orderedOrderKey,1); ?>"><?php _e('Vedi dettagli', 'bts'); ?></a> |
								<a href="<?php echo $page_link; ?>&amp;wc_pv_mark_ready=<?php echo intval(substr($orderedOrderKey,1)) ?>" class="mark-ready"><?php echo $orderedOrder['status']=='processing' ? __('Segna non pagato', 'bts') : __('Segna pagato', 'bts'); ?></a> <?php if ( $providers ) : ?>  <a href="#" class="view-order-tracking" id="<?php echo $order->id; ?>"><?php _e( 'Tracking', 'bts' ); ?></a><?php endif; ?></td>
							</tr>


							<tr id="view-items-<?php echo substr($orderedOrderKey,1); ?>" style="display:none;">
								<td colspan="6">
									<table class="details">
										<thead><tr><th>Quantity</th><th>Item</th><th>Links</th></tr></thead>
										<tbody>
											<?php foreach ($itemsForOrder[substr($orderedOrderKey,1)] as $key => $item):  ?>

											<tr>

												<?php $item_meta = new WC_Order_Item_Meta( $item[ 'item_meta' ] );
												$item_meta = $item_meta->display( false, true ); ?>

												<?php
												//var_dump($item);
												if(empty ($item['Filters'])) $item['Filters']='Originale';
												if(empty ($item['Vignette'])) $item['Vignette']='No Vignette';
												//echo '<td><input style="width:2.2em; margin-right:0.3em" value="'.$item['qty'] . '"></td><td>' . 'x ' . $item['name'].' | ' . $item['Filters'] .' | ' . $item['Vignette'] .'</td>';
												echo	'<td><div class="quantity buttons_added"><input class="minus" type="button" value="-"><input name="change-qty" class="input-text qty text" type="number" size="4" min="0" max="80" step="1" value="'.$item['qty'] . '"><input class="plus" type="button" value="+"></div></td>'.
														'<td>' . 'x ' . $item['name'].' | ' . $item['Filters'] .' | ' . $item['Vignette'] .'</td>';

												?>

												<td><a href="<?php echo $page_link; ?>&amp;wc_pv_change_qty=<?php echo substr($orderedOrderKey,1).'+'.$key.'+'.$item['product_id'].'+'; ?>" class="change-qty">Change quantity</a></td>

						<?php /* if (!empty( $item_meta ) && $item_meta != '<dl class="variation"></dl>') : ?>
							<?php echo $item_meta; ?>
						<?php endif; */ ?>

				</tr>



			<?php endforeach; ?>
		</tbody></table>
	</td>
</tr>

<?php endif; ?>
<?php endforeach; //foreach CustomerAsOrder ?>
<?php endforeach; //foreach ClassAsCustomer ?>
<tr class="<?php echo $strOrderReady; ?>">
    <?php if(!isset($_GET['c'])){?>
    <td rowspan="2" width="30%">
        <h2><a href="<?php echo $page_link; ?>&c=<?php echo $orderedClassKey; ?>"><?php $idObj = get_term_by('slug',$orderedClassKey, 'product_cat');
            echo $idObj->name; ?></a></h2></td>
    <?php }?>
	<td width="30%"><b>Totale:</b></td>
	<td colspan="4" width="30%"><?php echo woocommerce_price( $totals ); ?></td>
	<td rowspan="2"><a href="<?php echo $page_link; ?>&amp;wc_pv_mark_ready=<?php echo substr($strOrderList, 0, -1) ; ?>">Segna pagato</a></td>

</tr>

<tr>
	<td><b>Pagati:</b></td>
	<td colspan="4"><?php echo  $processing ; ?>/<?php echo  $ordercount ; ?></td>

</tr>
</tbody></table>
<?php endforeach; //foreach AllOrderAsClass ?>
<?php else : ?>
	<table>
		<tr>
			<td colspan="4"
			style="text-align:center;"><?php _e( 'Non c\'è ancora nessun ordine.', 'bts' ); ?></td>
		</tr>
	</table>

<?php endif; ?>


