<script type="text/javascript">
jQuery(function () {
	jQuery('a.view-items').on('click', function (e) {
		e.preventDefault();
		var id = jQuery(this).attr('id');

		if ( jQuery(this).text() == "<?php _e('Hide items', 'wcvendors'); ?>" ) {
			jQuery(this).text("<?php _e('View items', 'wcvendors'); ?>");
		} else {
			jQuery(this).text("<?php _e('Hide items', 'wcvendors'); ?>");
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

});
</script>

<h2><?php _e( 'Orders', 'wcvendors' ); ?></h2>



<?php global $woocommerce; ?>





<?php

/* ESECUZIONE COMANDI */


/* Cambio stato dell'ordine */
if( isset($_GET['wc_pv_mark_ready']) ){
	//TODO SECURITY
	$order=new WC_Order($_GET['wc_pv_mark_ready']);
	if($order->get_status() != 'processing')
		$order->update_status('processing',__('Order status changed from the dashboard.','bts'));
	else
		$order->update_status('on-hold',__('Order status changed from the dashboard.','bts'));
}



/* Cambio quantità */
if( isset($_GET['wc_pv_change_qty']) ){


	//TODO SECURITY




	$args = explode(' ', $_GET['wc_pv_change_qty']);

	if( (isset($args[3]) && intval($args[3])>=0 && intval($args[3])<=80 )  ):

		$product=new WC_Product($args[2]);

		if(!empty($product)) :
			$price=$product->price;

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
			$order->calculate_totals();
		endif;

	endif;

}

?>



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
			if($valid_item['Sold by'] == get_user_meta( $user_id, 'nickname') ){
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


			ksort($allOrders);

			//echo "<pre>"; var_dump($allOrders); echo "</pre>";

			foreach($allOrders as $orderedClassKey => $orderedClass ) :  ?>
			<h2><a href="<?php echo $page_link; ?>&c=<?php echo $orderedClassKey; ?>"><?php $idObj = get_term_by('slug',$orderedClassKey, 'product_cat');
  echo $idObj->name; ?></a></h2>

			<table class="table table-condensed table-vendor-sales-report">
				<?php if(isset($_GET['c'])) : ?>
				<thead>
					<tr>
						<th class="user-header"><?php _e( 'Customer', 'wcvendors' ); ?></th>
						<th class="product-header"><?php _e( 'Order', 'wcvendors' ); ?></th>
						<th class="commission-header"><?php _e( 'Total', 'wcvendors' ) ?></th>
						<th class="rate-header"><?php _e( 'Date', 'wcvendors' ) ?></th>
						<th class="status-header"><?php _e( 'Status', 'wcvendors' ) ?></th>
						<th class="rate-header"><?php _e( 'Links', 'wcvendors' ) ?></th>
					</tr>
				</thead>
				<?php endif; ?>
					<tbody>


						<?php


						$totals =0;
						$processing=0;
						$ordercount=0;

						foreach($orderedClass as $orderedCustomerKey => $orderedCustomer ) :



							ksort($orderedClass);
							foreach($orderedCustomer as $orderedOrderKey => $orderedOrder ) :

							$totals+=$orderedOrder['total'];
							$processing+= ($orderedOrder['status']=='processing');
							$ordercount++;
						?>
						<?php if(isset($_GET['c'])) : ?>
						<tr>
							<td><?php echo $orderedCustomerKey; ?></td>
							<td><?php echo $orderedOrderKey ?></td>


							<!-- <td><?php $sum = WCV_Queries::sum_for_orders( array( $order->id ), array('vendor_id'=>get_current_user_id()) ); $total = $sum[0]->line_total; $totals += $total; echo woocommerce_price( $total ); ?></td> -->
							<td><?php echo woocommerce_price($orderedOrder['total']); ?></td>
							<td><?php echo $orderedOrder['date']; ?></td>
							<td><?php echo $orderedOrder['status']; ?></td>
							<!--<td><a href="#" class="view-items" id="<?php echo $order->id; ?>"><?php _e('View details', 'wcvendors'); ?></a> | <a href="?wc_pv_mark_shipped=<?php echo $order->id; ?>" class="mark-shipped"><?php echo $shipped ? __('Unmark shipped', 'wcvendors') : __('Mark shipped', 'wcvendors'); ?></a> <?php if ( $providers ) : ?>  <a href="#" class="view-order-tracking" id="<?php echo $order->id; ?>"><?php _e( 'Tracking', 'wcvendors' ); ?></a><?php endif; ?></td> -->
							<td>
								<a href="#" class="view-items" id="<?php echo substr($orderedOrderKey,1); ?>"><?php _e('View details', 'wcvendors'); ?></a> |
								<a href="<?php echo $page_link; ?>&amp;wc_pv_mark_ready=<?php echo intval(substr($orderedOrderKey,1)) ?>" class="mark-ready"><?php echo $orderedOrder['status']=='processing' ? __('Unmark ready', 'wcvendors') : __('Mark ready', 'wcvendors'); ?></a> <?php if ( $providers ) : ?>  <a href="#" class="view-order-tracking" id="<?php echo $order->id; ?>"><?php _e( 'Tracking', 'wcvendors' ); ?></a><?php endif; ?></td>
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
												if(empty ($item['Filters'])) $item['Filters']='Originale';
												if(empty ($item['Vignette'])) $item['Vignette']='No Vignette';
												//echo '<td><input style="width:2.2em; margin-right:0.3em" value="'.$item['qty'] . '"></td><td>' . 'x ' . $item['name'].' | ' . $item['Filters'] .' | ' . $item['Vignette'] .'</td>';
												echo	'<td><div class="quantity buttons_added"><input class="minus" type="button" value="-"><input name="change-qty" class="input-text qty text" type="number" size="4" min="1" max="80" step="1" value="'.$item['qty'] . '"><input class="plus" type="button" value="+"></div></td>'.
														'<td>' . 'x ' . $item['name'].' | ' . $item['Filters'] .' | ' . $item['Vignette'] .'</td>';

												?>

												<td><a href="<?php echo $page_link; ?>&amp;wc_pv_change_qty=<?php echo substr($orderedOrderKey,1).'+'.$key.'+'.$item['product_id'].'+'; ?>" class="change-qty">Change quantity</a></td>

						<?php if (!empty( $item_meta ) && $item_meta != '<dl class="variation"></dl>') : ?>
							<?php echo $item_meta; ?>
						<?php endif; ?>

				</tr>



			<?php endforeach; ?>
		</tbody></table>
	</td>
</tr>

<?php endif; ?>
<?php endforeach; //foreach CustomerAsOrder ?>
<?php endforeach; //foreach ClassAsCustomer ?>
<tr>
	<td><b>Total:</b></td>
	<td colspan="4"><?php echo woocommerce_price( $totals ); ?></td>

</tr>

<tr>
	<td><b>Ready:</b></td>
	<td colspan="4"><?php echo  $processing ; ?>/<?php echo  $ordercount ; ?></td>

</tr>

</tbody></table>
<?php endforeach; //foreach AllOrderAsClass ?>




<?php else : ?>
	<table>
		<tr>
			<td colspan="4"
			style="text-align:center;"><?php _e( 'You have no orders.', 'wcvendors' ); ?></td>
		</tr>
	</table>

<?php endif; ?>


