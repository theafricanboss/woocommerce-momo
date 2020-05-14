<?php
/*
 * Plugin Name: MOMO - Mobile Money Payment Woocommerce Extension
 * Plugin URI: https://theafricanboss.com/momo
 * Description: Receive mobile money payments on your website with WooCommerce + MOMO
 * Author: The African Boss (theafricanboss@gmail.com)
 * Author URI: https://theafricanboss.com
 * Version: 2.5.0
 * Version Date: May 14, 2020
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
 * This action hook displays MOMO only for customers shipping over to certain countries
add_filter( 'woocommerce_available_payment_gateways', 'momo_gateway_for_country' );
function momo_gateway_for_country( $gateways ) {
	global $woocommerce;
	if ( isset( $gateways['momo'] ) && $woocommerce->customer->get_country() != 'RW' ) {
		unset( $gateways['momo'] );
	}
	return $gateways;
}
*/

/*
 * Dashboard Menu Button
 */
function momo_admin_menu(){
	add_menu_page( null, 'MOMO', 'manage_options', 'wc-settings&tab=checkout&section=momo', 'momo_admin_menu', 'dashicons-cart' );
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
$this->method_title = 'MOMO - Easy Mobile Money Payments (MOMO, GooglePay, ApplePay, Cash App, Venmo, Western Union, MoneyGram, etc)';
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
$this->ReceiverVenmo = $this->get_option( 'ReceiverVenmo' );
$this->ReceiverVenmoOwner = $this->get_option( 'ReceiverVenmoOwner' );
$this->ReceiverMOMOEmail = $this->get_option( 'ReceiverMOMOEmail' );

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
		'default'     => 'MOMO - Easy Mobile Money Payments (MOMO, GooglePay, ApplePay, Cash App, Venmo, Western Union, MoneyGram, etc)',
		'placeholder' => 'MOMO - Easy Mobile Money Payments (MOMO, GooglePay, ApplePay, Cash App, Venmo, Western Union, MoneyGram, etc)',
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
	'ReceiverVenmo' => array(
		'title'       => 'Receiver Venmo account',
		'type'        => 'text',
		'description' => 'This is the Venmo account associated with your store Venmo account. Customers will send money to this Venmo account',
		'default'     => '@',
		'placeholder' => '@venmoid',
	),
	'ReceiverVenmoOwner' => array(
		'title'       => "Receiver Venmo Owner's Name",
		'type'        => 'text',
		'description' => 'This is the name associated with your store Venmo account. Customers will send money to this Venmo account name',
		'placeholder' => 'Snoop D',
	),
	'ReceiverMOMOEmail' => array(
		'title'       => "Receiver Mobile Money Owner's Email",
		'type'        => 'text',
		'description' => 'This is the email associated with your store mobile money account or your receiving Mobile Money account. Customers will send money to this email',
		'default'     => "@gmail.com",
		'placeholder' => "email@website.com",
	),
);
		
}
 
/**
 * Custom form
 */
public function payment_fields() {
echo '<fieldset id="wc-' . esc_attr( $this->id ) . 'form" style="background:white; padding:5%">';

// Add this action hook if you want your custom payment gateway to support it
do_action( 'woocommerce_form_start', $this->id );

echo 'Please select your mobile money payment method to send the requested total amount via an online mobile money platform (CashApp, Venmo, GooglePay, ApplePay, Western Union, Moneygram, etc) or via a local mobile money agent and fill these fields out below to confirm that you have sent the total requested amount.',"<br>","<br>";
echo '
	<div class="form-row form-row-wide">
	<label>Payment Transfer Method used <span class="required">*</span></label>
		<select id="momo-MOMOApp" name="MOMOApp" style="width:95%; border:1px solid" type="text" placeholder="Please select an option from the dropdown" autocomplete="off">
			<option>Select a different option from this dropdown</option>
			<option value="agent">MOMO Agent to ', wpautop( wp_kses_post( $this->ReceiverMOMONo ) ), "Registered under ", wpautop( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
			<option value="cashapp">Cash App ', wpautop( wp_kses_post( $this->ReceiverCashApp ) ), "Registered under ", wpautop( wp_kses_post( $this->ReceiverCashAppOwner ) ),'</option>
			<option value="venmo">Venmo App ', wpautop( wp_kses_post( $this->ReceiverVenmo ) ), "Registered under ", wpautop( wp_kses_post( $this->ReceiverVenmoOwner ) ),'</option>
			<option value="googlepay">Google Pay ', wpautop( wp_kses_post( $this->ReceiverMOMOEmail ) ), '</option>
			<option value="applepay">Apple Pay ', wpautop( wp_kses_post( $this->ReceiverMOMOEmail ) ), '</option>
			<option value="westernunion">Western Union to ', wpautop( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
			<option value="moneygram">MoneyGram to ', wpautop( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
			<option value="worldremit">World Remit to ', wpautop( wp_kses_post( $this->ReceiverMOMONoOwner ) ), '</option>
		</select>
	</div>

	<div class="form-row form-row-wide">
	<label>Sender Name <span class="required">*</span></label>
	<input id="momo-CustomerMOMOName" style="text-transform:uppercase; width:95%; border:1px solid" name="CustomerMOMOName" type="text" placeholder="Insert Full Name" autocomplete="off">
	</div>

	<div class="form-row form-row-wide">
	<label>Sender Phone Number<span class="required">*</span></label>
	<input id="momo-CustomerMOMONo" name="CustomerMOMONo" style="width:95%; border:1px solid" type="text" min="111111" size="12" placeholder="+1234567890" autocomplete="off">
	</div>

	<div class="form-row form-row-wide">
	<label>Reference/Transaction Confirmation Code (if applicable)</label>
	    <input id="momo-MOMORefNo" name="MOMORefNo" style="width:95%; border:1px solid" type="text" placeholder="Insert Confirmation Code" autocomplete="off">
	</div>

	<div class="form-row form-row-first">
		<label>Comfirm Amount Sent <span class="required">*</span></label>
	    <input id="momo-MOMOAmountNo" name="MOMOAmountNo" style="width:90%; border:1px solid" type="number" min="0" placeholder="Insert Amount" autocomplete="off">
	</div>
	<div class="form-row form-row-last">
		<label>Currency Code USD/RWF/etc <span class="required">*</span></label>
	    <input id="momo-MOMOCurrencyCode" style="text-transform:uppercase; width:90%; border:1px solid" name="MOMOCurrencyCode" type="text" placeholder="RWF" autocomplete="off">
	</div>

	<div class="clear"></div>';

echo "<br>", 'See this ', "<a href='https://theafricanboss.com/momodemo' style='text-decoration: underline' target='_blank'>", '1min video demo ' ,"</a>" , 'explaining how this works.', "<br>";
echo 'If you are having an issue, please contact ', '<a href="mailto:', wp_kses_post($this->ReceiverMOMOEmail) ,'" target="_blank">', wp_kses_post($this->ReceiverMOMOEmail) ,"</a>";
echo "<br><br>", "<a href='https://theafricanboss.com/momo' style='text-decoration: underline' target='_blank'>", 'Plugin by The African Boss ' ,"</a>" , "<br>";

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
	$customerMomoName = trim($_POST[ 'CustomerMOMOName' ]);
	$customerMomoNumber = trim($_POST[ 'CustomerMOMONo' ]);
	$customerMomoAmount = trim($_POST[ 'MOMOAmountNo' ]);
	$customerMomoCurrencyCode = trim($_POST[ 'MOMOCurrencyCode' ]);
	$customerMomoCurrencyCode = strtoupper($customerMomoCurrencyCode);
	$customerPaymentMode = trim($_POST[ 'MOMOApp' ]);
	
	$sum = 0;

	if( empty( $_POST[ 'CustomerMOMOName' ]) || strlen($customerMomoNumber) < 3 ) {
		wc_add_notice(  'Mobile Money Customer Name is invalid!', 'error' );
		$sum = $sum++;
	}
	if( empty( $_POST[ 'CustomerMOMONo' ]) || strlen($customerMomoNumber) < 10 || is_numeric($customerMomoNumber) ) {
		wc_add_notice(  'Mobile Money Customer Phone Number is invalid!', 'error' );
		$sum = $sum++;
	}

	/*
	if( empty( $_POST[ 'MOMORefNo' ]) ) {
		wc_add_notice(  'Mobile Money Reference/Transaction code is required!', 'error' );
		$sum = $sum++;
	}
	*/

	if( empty( $_POST[ 'MOMOAmountNo' ]) || is_numeric($customerMomoAmount)) {
		wc_add_notice(  'Mobile Money Transferred Amount is invalid!', 'error' );
		$sum = $sum++;
	}

	if( empty( $_POST[ 'MOMOCurrencyCode' ]) || strlen($customerMomoCurrencyCode) < 2  || strlen($customerMomoCurrencyCode) > 4) {
		wc_add_notice(  'Mobile Money currency code is invalid!', 'error' );
		$sum = $sum++;
	}

	if( empty( $_POST[ 'MOMOApp' ] ) ) {
		wc_add_notice(  'Payment Transfer Method is invalid!', 'error' );
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
	global $woocommerce;

	// we need it to get any order details
	$order = wc_get_order( $order_id );


	if(validate_fields() == true) {
		if( !is_wp_error($order) ) {

			// we received the payment
			$order->payment_complete();
			$order->reduce_order_stock();
	
			$MOMORefNo = $_POST['MOMORefNo'];
			$MOMOAmountNo = $_POST['MOMOAmountNo'];
			$CustomerMOMOName = $_POST['CustomerMOMOName'];
			$CustomerMOMONo = $_POST['CustomerMOMONo'];
			$MOMOCurrencyCode = $_POST['MOMOCurrencyCode'];
			$MOMOApp = $_POST['MOMOApp'];
	
			$note = 'Dear ' .  $CustomerMOMOName . ', your order application was received!'.'<br><br>'.
				'We are checking our systems to confirm that we received the '."<strong style='text-transform:uppercase;'>". $MOMOAmountNo . $MOMOCurrencyCode ."</strong>".
				' sent by ' ."<strong style='text-transform:uppercase;'>".  $CustomerMOMOName ."</strong>". ' using the following mobile money phone number: ' ."<strong>". $CustomerMOMONo ."</strong>".
				' along with the following MOMO Reference Code ' ."<strong>". $MOMORefNo ."</strong>". ' sent using ' . $MOMOApp . ' so we can proceed with the shipping and delivery options you chose.'.'<br><br>'.
				'Thank you for doing business with us' .  $CustomerMOMOName . '!'.'<br>'.
				' You will be updated regarding your order details soon'.'<br>'.
				'Kindly,'.'<br>'. 'Store Assistant';
	
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
