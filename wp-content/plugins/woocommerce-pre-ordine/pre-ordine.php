﻿<?php
/*
Plugin Name: WooCommerce Pre Ordine Gateway
Plugin URI: http://woothemes.com/woocommerce
Description: Extends WooCommerce with an Pre Ordine gateway.
Version: 1.0
Author: WooThemes
Author URI: http://woothemes.com/
Copyright: © 2009-2011 WooThemes.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
add_action('plugins_loaded', 'woocommerce_gateway_pre_ordine_init', 0);
function woocommerce_gateway_pre_ordine_init() {
if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
/**
* Localisation
*/
load_plugin_textdomain('wc-gateway-pre_ordine', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
/**
* Gateway class
*/
class WC_Gateway_Pre_ordine extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     */
	public function __construct() {
		$this->id                 = 'pre_ordine';
		$this->icon               = apply_filters('woocommerce_pre_ordine_icon', '');
		$this->has_fields         = false;
		$this->method_title       = __( 'Pre ordine', 'woocommerce' );
		$this->method_description = __( 'Consenti di effettuare un pre_ordine, il cliente non può effettuare il pagfamento finchè non sia stata verificata l\'esistenza dei prodotti in serra.', 'woocommerce' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->title        = $this->get_option( 'title' );
		$this->description  = $this->get_option( 'description' );
		$this->instructions = $this->get_option( 'instructions', $this->description );

		// Actions
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    	add_action( 'woocommerce_thankyou_pre_ordine', array( $this, 'thankyou_page' ) );

    	// Customer Emails
    	add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
    }

    /**
     * Initialise Gateway Settings Form Fields
     */
    public function init_form_fields() {

    	$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Abilita il pre-ordine', 'woocommerce' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Pre ordine', 'woocommerce' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Descrizione del metodo di pagamento che il cliente visualizzerà nella pagina di checkout.', 'woocommerce' ),
				'default'     => __( 'Completando l\'acquisto delle foto ti impegni a livello legale al pagamento del tuo acquisto.' , 'woocommerce' ),
				'desc_tip'    => true,
			),
			'instructions' => array(
				'title'       => __( 'Instructions', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
		);
    }

    /**
     * Output for the order received page.
     */
	public function thankyou_page() {
		if ( $this->instructions )
        	echo wpautop( wptexturize( $this->instructions ) );
	}

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     */
	public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
        if ( $this->instructions && ! $sent_to_admin && 'pre_ordine' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
			echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
		}
	}

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Mark as on-hold (we're awaiting the pre_ordine)
		$order->update_status( 'on-hold', __( 'Awaiting pre_ordine payment', 'woocommerce' ) );

		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);
	}
}
/**
* Add the Gateway to WooCommerce
**/
function woocommerce_add_gateway_pre_ordine_gateway($methods) {
$methods[] = 'WC_Gateway_Pre_ordine';
return $methods;
}
add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_pre_ordine_gateway' );
} 