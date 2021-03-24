<?php
/*
 * Plugin Name: MOMO - Mobile Money Payments Woocommerce Extension
 * Plugin URI: https://theafricanboss.com/momo
 * Description: Receive mobile money payments from any country and carrier on your website with WooCommerce + MOMO
 * Author: The African Boss (theafricanboss@gmail.com)
 * Author URI: https://theafricanboss.com
 * Version: 2.6.1
 * Version Date: June 27, 2020
 * Created: 2019
 * Copyright 2020 theafricanboss.com All rights reserved
 */
 
// Reach out to The African Boss for website and mobile app development services at theafricanboss@gmail.com
// or at www.TheAfricanBoss.com or download our app at www.TheAfricanBoss.com/app

// If you are using this version, please send us some feedback
//via email at theafricanboss@gmail.com on your thoughts and what you would like improved

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce fallback notice.
 * @return string
 */
function woocommerce_momo_missing_wc_notice() {
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'MOMO requires WooCommerce to be installed and active. You can download %s here.', 'WC_MOMO_Gateway' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'momo_add_gateway_class' );
function momo_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_MOMO_Gateway'; // your class name is here
	return $gateways;
}


/*
 * Dashboard Menu Button
 */
function momo_admin_menu(){
	add_menu_page( null, 'MOMO', 'manage_options', 'wc-settings&tab=checkout&section=momo', 'momo_admin_menu', 'dashicons-cart' );
	add_submenu_page( 'wc-settings&tab=checkout&section=momo', 'Upgrade MOMO', 'Upgrade', 'manage_options', 'https://theafricanboss.com/momo', null, null );
}
add_action('admin_menu','momo_admin_menu');

/*
 * Settings Button
 */
function momo_settings_link( $links_array ){
	array_unshift( $links_array, '<a href="/wp-admin/admin.php?page=wc-settings&tab=checkout&section=momo">Settings</a>' );
	return $links_array;
}
$plugin = plugin_basename(__FILE__); 
add_filter( "plugin_action_links_$plugin", 'momo_settings_link' );

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'momo_init_gateway_class' );
function momo_init_gateway_class() {
    
if ( ! class_exists( 'WooCommerce' ) ) {
    add_action( 'admin_notices', 'woocommerce_momo_missing_wc_notice' );
    return;
}

class WC_MOMO_Gateway extends WC_Payment_Gateway {

/**
 * Class constructor
 */
public function __construct() {
$this->id = 'momo'; // payment gateway plugin ID
$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
$this->has_fields = true; // in case you need a custom form
$this->method_title = 'MOMO - Easy Mobile Money Payments (MOMO, Cash App, Western Union, MoneyGram)';
$this->method_description = 'Easily receive mobile money payments'; // will be displayed on the options page

// gateways can support subscriptions, refunds, saved payment methods
$this->supports = array(
	'products'
);

// Method with all the options fields
$this->init_form_fields();

// Load the settings.
$this->init_settings();
$this->title = $this->get_option( 'title' );
$this->description = $this->get_option( 'description' );
$this->enabled = $this->get_option( 'enabled' );
$this->ReceiverMOMONo = $this->get_option( 'ReceiverMOMONo' );
$this->ReceiverMOMONoOwner = $this->get_option( 'ReceiverMOMONoOwner' );
$this->ReceiverCashApp = $this->get_option( 'ReceiverCashApp' );
$this->ReceiverCashAppOwner = $this->get_option( 'ReceiverCashAppOwner' );
$this->ReceiverMOMOEmail = $this->get_option( 'ReceiverMOMOEmail' );
$this->toggleTutorial = $this->get_option( 'toggleTutorial' );
$this->toggleCredits = $this->get_option( 'toggleCredits' );

// This action hook saves the settings
add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

// We need custom JavaScript to obtain a token
add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

}

	
/**
 * Plugin options
 */
public function init_form_fields(){
$this->form_fields = array(
	'enabled' => array(
		'title'       => 'Enable/Disable',
		'label'       => 'Enable MOMO',
		'type'        => 'checkbox',
		'description' => '',
		'default'     => 'no'
	),
	'title' => array(
		'title'       => 'Title',
		'type'        => 'text',
		'description' => 'This is the title which the user sees during checkout.',
		'default'     => 'MOMO - Easy Mobile Money Payments (MOMO, Cash App, Western Union, MoneyGram)',
		'placeholder' => 'MOMO - Easy Mobile Money Payments (MOMO, Cash App, Western Union, MoneyGram)',
		'desc_tip'    => true,
	),
	'description' => array(
		'title'       => 'Description',
		'type'        => 'textarea',
		'description' => 'This is the description which the user sees during checkout.',
		'default'     => 'Pay with mobile money using this user-friendly payment gateway',
		'desc_tip'    => true,
	),
	'ReceiverMOMONo' => array(
		'title'       => 'Receiver Mobile Money No',
		'type'        => 'text',
		'description' => 'This is the phone number associated with your store mobile money account or your receiving Mobile Money account. Customers will send money to this number',
		'placeholder' => "+1234567890",
	),
	'ReceiverMOMONoOwner' => array(
		'title'       => "Receiver Mobile Money Owner's Name",
		'type'        => 'text',
		'description' => 'This is the name associated with your store mobile money account or your receiving Mobile Money account. Customers will send money to this name',
		'placeholder' => "John D",
	),
	'ReceiverCashApp' => array(
		'title'       => 'Receiver Cash App account',
		'type'        => 'text',
		'description' => 'This is the Cash App account associated with your store Cash App account. Customers will send money to this Cash App account',
		'default'     => '$',
		'placeholder' => '$cashId',
	),
	'ReceiverCashAppOwner' => array(
		'title'       => "Receiver Cash App Owner's Name",
		'type'        => 'text',
		'description' => 'This is the name associated with your store Cash App account. Customers will send money to this Cash App account name',
		'placeholder' => 'Jane D',
	),
	'ReceiverMOMOEmail' => array(
		'title'       => "Receiver Mobile Money Owner's Email",
		'type'        => 'text',
		'description' => 'This is the email associated with your store mobile money account or your receiving Mobile Money account. Customers will send money to this email',
		'default'     => "@gmail.com",
		'placeholder' => "email@website.com",
	),
	'toggleTutorial' => array(
		'title'       => 'Enable/Disable',
		'label'       => 'Enable Tutorial to display 1min video link',
		'type'        => 'checkbox',
		'description' => 'Help your customers checkout with ease by showing this tutorial link',
		'default'     => 'no',
	),
	'toggleCredits' => array(
		'title'       => 'Enable/Disable',
		'label'       => 'Enable Credits to display Powered by The African Boss',
		'type'        => 'checkbox',
		'description' => 'Help us spread the word about this plugin by sharing that we made this plugin',
		'default'     => 'no',
	),
);
		
}
	
	
/**
 * Custom form 
 */
public function payment_fields () {
	global $woocommerce, $total;
	
	// we need it to get any order details
	$order = wc_get_order( $order_id );

	$woocommerce->cart->get_cart();
	$total = $woocommerce->cart->get_total();
	
	
echo '<fieldset id="wc-' . esc_attr( $this->id ) . 'form" style="background:white; padding:5%">';

// Add this action hook if you want your custom payment gateway to support it
do_action( 'woocommerce_form_start', $this->id );
	
echo 'Please select your mobile money payment method to send the requested total amount via an online mobile money platform (CashApp, Venmo, GooglePay, ApplePay, Western Union, Moneygram, etc) or via a local mobile money agent and fill these fields out below to confirm that you have sent the total requested amount.',"<br>","<br>";
echo '

	<div class="form-row form-row-wide">
	
	<label>Payment Transfer Method used <span class="required">*</span></label>
		<select id="', esc_attr(momo-MOMOApp) , '" name="', esc_attr(MOMOApp) , '" style="width:95%; border:1px solid" type="text" autocomplete="off">
			<option>Please select a mobile payment method of your choice</option>
			<option value="', esc_attr(agent) , '">MOMO Agent to ', esc_html( wp_kses_post( $this->ReceiverMOMONo ) ), "Registered under ", esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
			<option value="', esc_attr(cashapp) , '">Cash App ', esc_html( wp_kses_post( $this->ReceiverCashApp ) ), "Registered under ", esc_html( wp_kses_post( $this->ReceiverCashAppOwner ) ),'</option>
			<option value="', esc_attr(westernunion) , '">Western Union to ', esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
			<option value="', esc_attr(moneygram) , '">MoneyGram to ', esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
			<option value="', esc_attr(worldremit) , '">World Remit to ', esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
		</select>
	</div>

	<div class="form-row form-row-wide">
	<label>Sender Name <span class="required">*</span></label>
	<input id="', esc_attr(momo-CustomerMOMOName) , '" style="text-transform:uppercase; width:95%; border:1px solid" name="', esc_attr(CustomerMOMOName) , '" type="text" placeholder="Insert Full Name" autocomplete="off">
	</div>
	
	<div class="form-row form-row-wide">
	<label>Sender Phone Number <span class="required">*</span></label>
	<input id="', esc_attr(momo-CustomerMOMONo) , '" name="', esc_attr(CustomerMOMONo) , '" style="width:95%; border:1px solid" type="text" min="111111" size="12" placeholder="+1234567890" autocomplete="off">
	</div>

	<div class="form-row form-row-wide">
	<label>Reference/Transaction Confirmation Code (if applicable)</label>
	    <input id="', esc_attr(momo-MOMORefNo) , '" name="', esc_attr(MOMORefNo) , '" style="width:95%; border:1px solid" type="text" placeholder="Insert Confirmation Code" autocomplete="off">
	</div>

	<div class="form-row form-row-wide">
		<label style="width:95%;">Please verify that the Amount Sent is the following: <span class="required">***</span></label>
		<p style="width:95%" class="input-group-text"><span style="margin:0 10px 0 0; font-weight: bold;">' . $total . '</span> <span style="font-size: 0.7em;"><em>(Add any applicable transfer fees)</em></span></p>
	</div>

	<div class="clear"></div>';
	
	// if toggle Tutorial is disabled, we do not show credits
	if ( 'no' === $this->toggleTutorial ) {
		echo '<br>';
	} else {
		echo "<br>", 'See this ', "<a href=" , esc_url('https://theafricanboss.com/momodemo') . " style='text-decoration: underline' target='_blank'>", '1min video demo ' ,"</a>" , 'explaining how this works.', "<br>";
	}
	
	echo 'If you are having an issue, please call <a href="tel:', esc_html( wp_kses_post($this->ReceiverMOMONo)) ,'" target="_blank">', esc_html( wp_kses_post($this->ReceiverMOMONo)) ,'</a> or email <a href="mailto:', esc_html( wp_kses_post($this->ReceiverMOMOEmail)) ,'" target="_blank">', esc_html( wp_kses_post($this->ReceiverMOMOEmail)) ,'</a>';
	
	
	// if toggle Credits is disabled, we do not show credits
	if ( 'no' === $this->toggleCredits ) {
		echo '<br>';
	} else {
		echo '<br><br> <a href=' , esc_url("https://theafricanboss.com/momo") . ' style="text-decoration: underline;" target="_blank">Powered by The African Boss</a><br>';
	}

	do_action( 'woocommerce_form_end', $this->id );

	echo '<div class="clear"></div></fieldset>';
}

/*
 * Payment Custom JS and CSS
 */
public function payment_scripts() {

	// we need JavaScript to process a token only on cart/checkout pages, right?
	if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
		return;
	}

	// if our payment gateway is disabled, we do not have to enqueue JS too
	if ( 'no' === $this->enabled ) {
		return;
	}

	wp_enqueue_script( 'woocommerce_momo' );

}

/*
 * Fields validation
 */
public function validate_fields() {
	$customerPaymentMode = trim($_POST[ 'MOMOApp' ]);
	$customerMomoName = sanitize_text_field(trim($_POST[ 'CustomerMOMOName' ]));
	$customerMomoNumber = sanitize_text_field(trim($_POST[ 'CustomerMOMONo' ]));
	
	$sum = 0;

	if( empty( sanitize_text_field(trim($_POST[ 'MOMOApp' ] )) )) {
		wc_add_notice(  'Payment Transfer Method is invalid!', 'error' );
		$sum = $sum++;
	}

	if( empty( sanitize_text_field(trim($_POST[ 'CustomerMOMOName' ])) || sanitize_text_field(trim(strlen($customerMomoNumber))) < 3 )) {
		wc_add_notice(  'Mobile Money Customer Name is invalid!', 'error' );
		$sum = $sum++;
	}

	if( empty( sanitize_text_field(trim($_POST[ 'CustomerMOMONo' ]))) || sanitize_text_field(trim(strlen($customerMomoNumber))) < 10 || sanitize_text_field(trim(is_numeric($customerMomoNumber))) ) {
		wc_add_notice(  'Mobile Money Customer Phone Number is invalid!', 'error' );
		$sum = $sum++;
	}

	if( $sum == 0 ) {
	    return true;
	} else {
		return false;
	}
}

/*
 * Process Payment
 */
public function process_payment( $order_id ) {
	global $woocommerce, $total;
	
	// we need it to get any order details
	$order = wc_get_order( $order_id );

	$woocommerce->cart->get_cart();
	$total = $woocommerce->cart->get_total();


	if(validate_fields() == true) {
		if( !is_wp_error($order) ) {

			// we received the payment
			$order->payment_complete();
			$order->reduce_order_stock();
	
			$MOMOApp = sanitize_text_field(trim($_POST['MOMOApp']));
			$CustomerMOMOName = sanitize_text_field(trim($_POST['CustomerMOMOName']));
			$CustomerMOMONo = sanitize_text_field(trim($_POST['CustomerMOMONo']));
			$MOMORefNo = sanitize_text_field(trim($_POST['MOMORefNo']));
	
			$note = 'Dear ' .  esc_html( $CustomerMOMOName ) . ', your order application was received!'.'<br><br>'.
				'We are checking our systems to confirm that we received the <strong style="text-transform:uppercase;">' . esc_html( $total ) . '</strong> sent by <strong style="text-transform:uppercase;">'.  esc_html( $CustomerMOMOName ) . '</strong> using the following mobile money phone number: <strong>'. esc_html( $CustomerMOMONo ) . '</strong> along with the following MOMO reference code <strong>' . esc_html( $MOMORefNo ) . '</strong> sent using ' . esc_html( $MOMOApp ) . ' so we can proceed with the shipping and delivery options you chose.'.'<br><br>'.
				'Thank you for doing business with us, ' . esc_html( $CustomerMOMOName ) . '!<br> You will be updated regarding your order details soon<br>'.
				'Kindest Regards,<br>'.
				'Gura Store Assistant';
	
			// some notes to customer (replace true with false to make it private)
			$order->add_order_note( $note , true );
	
			// Empty cart
			$woocommerce->cart->empty_cart();
	
			// Redirect to the thank you page
			return array(
				'result' => 'success',
				'redirect' => $this->get_return_url( $order )
			);
	
		} else {
			wc_add_notice(  'Connection error.', 'error' );
			return;
		}
	} else {
		wc_add_notice(  'Validation error', 'error' );
		return;
	}

     
}

	public function webhook() {
		$order = wc_get_order( $_GET['id'] );
		$order->payment_complete();
		$order->reduce_order_stock();
		
		update_option('webhook_debug', $_GET);
	}
}
	
}

// Reach out to The African Boss for website and mobile app development services at theafricanboss@gmail.com
// or at www.TheAfricanBoss.com or download our app at www.TheAfricanBoss.com/app

// If you are using this version, please send us some feedback
//via email at theafricanboss@gmail.com on your thoughts and what you would like improved