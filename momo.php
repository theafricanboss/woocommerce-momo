<?php
/*
 * Plugin Name: Checkout with Mobile Money, Western Union, MoneyGram, WorldRemit
 * Plugin URI: https://theafricanboss.com/momo
 * Description: Receive mobile money payments from any country and carrier on your website with WooCommerce + MOMO (Use for MOMO, Western Union, MoneyGram, WorldRemit)
 * Author: The African Boss
 * Author URI: https://theafricanboss.com
 * Text Domain: wc-momo
 * Version: 4.0
 * WC requires at least: 4.0.0
 * WC tested up to: 5.6.0
 * Version Date: Aug 28, 2021
 * Created: 2019
 * Copyright 2021 theafricanboss.com All rights reserved
 */

 // renamed from MOMO - Mobile Money Payments Woocommerce Extension
// Reach out to The African Boss for website and mobile app development services at theafricanboss@gmail.com
// or at www.TheAfricanBoss.com or download our app at www.TheAfricanBoss.com/app

// If you are using this version, please send us some feedback
//via email at theafricanboss@gmail.com on your thoughts and what you would like improved

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
include_once( ABSPATH . 'wp-includes/pluggable.php');
include_once( ABSPATH . 'wp-includes/option.php');

define('WCMOMO_PLUGIN_DIR', plugin_dir_path(__FILE__) );
define('WCMOMO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define('WCMOMO_PLUGIN_DIR_URL', plugins_url( '/' , __FILE__ ));

/**
 * WooCommerce fallback notice.
 * @return string
 */
function wcmomo_missing_wc_notice() {
	echo '<div class="error"><p><strong>' , sprintf( esc_html__( 'MOMO requires WooCommerce to be installed and active. You can download %s here.' , 'WC_MOMO_Gateway' ) , '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) , '</strong></p></div>';
}

// MOMO PRO
if ( current_user_can( 'manage_options' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-warning is-dismissible"><p>You are currently not using our MOMO PRO plugin. <strong>Please <a href="http://theafricanboss.com/momo" target="_blank">upgrade</a> for a better experience</strong></p></div>';
	});

	if ( is_plugin_active('wc-momo-pro/momo.php') ){
		deactivate_plugins( WCMOMO_PLUGIN_BASENAME );
		activate_plugin( 'wc-momo-pro/momo.php');
		wp_die( '<div><p>MOMO has been deactivated because the PRO version is activated.
		<strong>Enjoy the upgrade</strong></p></div>
		<div><a href="' .  esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=momo', __FILE__ ) ) . '">Set up the plugin</a> | <a href="' . admin_url('plugins.php') . '">Return</a></div>' );
	}
}

/*
 * Settings Button
 */
function wcmomo_settings_link( $links_array ){
	array_unshift( $links_array, '<a href="' .  esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=momo', __FILE__ ) ) . '">Settings</a>' );

	if ( !is_plugin_active( esc_url( plugins_url( 'wc-momo-pro/momo.php', dirname(__FILE__) ) ) ) ){
		$links_array['momo_pro'] = sprintf('<a href="https://theafricanboss.com/momo/" target="_blank" style="color: #39b54a; font-weight: bold;"> Get Pro now available at $19 </a>');
	}

	return $links_array;
}
$plugin = plugin_basename(__FILE__);
add_filter( "plugin_action_links_$plugin" , 'wcmomo_settings_link' );

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways' , 'wcmomo_add_gateway_class' );
function wcmomo_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_MOMO_Gateway'; // your class name is here
	return $gateways;
}

/*
 * Dashboard Menu Button
 */
function wcmomo_admin_menu(){
	$parent_slug = 'wc-settings&tab=checkout&section=momo';
	$capability = 'manage_options';

	add_menu_page( null , 'MOMO' , $capability , $parent_slug , 'wcmomo_admin_menu' , 'dashicons-money-alt' );
	add_submenu_page( $parent_slug , 'Upgrade MOMO' , '<span style="color:#99FFAA">Get Pro >> </span>' , $capability , 'https://theafricanboss.com/momo' , null, null );
	add_submenu_page( $parent_slug , 'Our Plugins' , '<span style="color:yellow">Free Recommended Plugins</span>' , $capability , admin_url("plugin-install.php?s=theafricanboss&tab=search&type=author") 	, null, null );
	add_submenu_page( $parent_slug , 'Feature my store' , 'Get Featured' , $capability , 'https://theafricanboss.com/momo#feature' , null, null );
	add_submenu_page( $parent_slug , 'Review MOMO' , 'Review' , $capability , 'https://wordpress.org/support/plugin/momo-mobile-money-payments-woocommerce-extension/reviews/?filter=5' , null, null );
}
add_action('admin_menu','wcmomo_admin_menu');

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded' , 'wcmomo_init_gateway_class' );
function wcmomo_init_gateway_class() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices' , 'wcmomo_missing_wc_notice' );
		return;
	}

	add_action('admin_menu' , 'wcmomo_admin_menu');

	class WC_MOMO_Gateway extends WC_Payment_Gateway {

		/**
		 * Class constructor
		 */
		public function __construct() {
		$this->id = 'momo'; // payment gateway plugin ID
		$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
		$this->has_fields = true; // in case you need a custom form
		$this->method_title = 'MOMO - Easy Mobile Money Payments (MOMO, Western Union, MoneyGram, WorldRemit)';
		$this->method_description = 'Easily receive mobile money payments'; // will be displayed on the options page

		// gateways can support subscriptions, refunds, saved payment methods
		$this->supports = array(
			'products'
		);

		// Method with all the options fields
		$this->wcmomo_init_form_fields();

		// Load the settings.
		$this->init_settings();
		$this->enabled = $this->get_option( 'enableMOMO' );
		$this->title = $this->get_option( 'checkout_title' );
		$this->ReceiverMOMONo = $this->get_option( 'ReceiverMOMONo' );
		$this->ReceiverMOMONoOwner = $this->get_option( 'ReceiverMOMONoOwner' );
		$this->ReceiverMOMOEmail = $this->get_option( 'ReceiverMOMOEmail' );
		$this->toggleMomoFree = $this->get_option( 'toggleMomoFree' );
		$this->MOMOCarrier = $this->get_option( 'MOMOCarrier' );
		$this->toggleMoneygramFree = $this->get_option( 'toggleMoneygramFree' );
		$this->toggleWorldremitFree = $this->get_option( 'toggleWorldremitFree' );
		$this->toggleWesternunionFree = $this->get_option( 'toggleWesternunionFree' );
		$this->checkout_description = $this->get_option( 'checkout_description' );
		$this->momo_notice = $this->get_option( 'momo_notice' );
		$this->store_instructions = $this->get_option( 'store_instructions' );
		$this->toggleTutorial = $this->get_option( 'toggleTutorial' );
		$this->toggleCredits = $this->get_option( 'toggleCredits' );

		// This action hook saves the settings
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// We need custom JavaScript to obtain a token
		// add_action( 'wp_enqueue_scripts' , array( $this , 'payment_scripts' ) );

		//Thank you page
		add_action( 'woocommerce_before_thankyou' , array( $this , 'wcmomo_thankyou_page' ) );

		// Customer Emails.
		add_action( 'woocommerce_email_before_order_table' , array( $this , 'wcmomo_instructions_sent' ), 10, 3 );

		}


		/**
		 * Plugin options
		 */
		public function wcmomo_init_form_fields(){

			$this->form_fields = array(
				'enableMOMO' => array(
					'title'       => 'Enable MOMO',
					'label'       => 'Check to Enable/Uncheck to Disable',
					'type'        => 'checkbox',
					'default'     => 'no'
				),
				'checkout_title' => array(
					'title'       => 'Checkout Title',
					'type'        => 'text',
					'description' => 'This is the title which the user sees on the checkout page.',
					'default'     => 'MOMO, Western Union, MoneyGram, WorldRemit',
					'placeholder' => 'MOMO - Easy Mobile Money Payments (MOMO, Western Union, MoneyGram, WorldRemit)',
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
				'ReceiverMOMOEmail' => array(
					'title'       => "Receiver Mobile Money Owner's Email",
					'type'        => 'text',
					'description' => 'This is the email associated with your store mobile money account or your receiving Mobile Money account. Customers will send money to this email',
					'placeholder' => "email@website.com",
				),
				'toggleMomoFree' => array(
					'title'       => 'Enable Mobile Money  <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'text',
					'description' => 'To disable this payment method, <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => 'enabled by default in the free version',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'MOMOCarrier' => array(
					'title'       => 'MOMO Carrier or Agent <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'text',
					'description' => 'Replace this with the carrier of your choice <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => 'A local/online MOMO Agent',
					'placeholder'     => 'A local/online MOMO Agent',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'toggleWorldremitFree' => array(
					'title'       => 'Enable Worldremit <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'text',
					'description' => 'To disable this payment method, <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => 'enabled by default in the free version',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'toggleMoneygramFree' => array(
					'title'       => 'Enable Moneygram <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'text',
					'description' => 'To disable this payment method, <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => 'enabled by default in the free version',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'toggleWesternunionFree' => array(
					'title'       => 'Enable Western Union <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'text',
					'description' => 'To disable this payment method, <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => 'enabled by default in the free version',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'checkout_description' => array(
					'title'       => 'Checkout Page Notice <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'textarea',
					'description' => 'This is the description which the user sees during checkout. <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => 'Use an online mobile money platform (Western Union, Moneygram, WorldRemit) or via a local/online mobile money agent.',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'momo_notice'    => array(
					'title'       => 'Thank You Notice <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'textarea',
					'description' => 'Notice that will be added to the thank you page before store instructions if any. <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => "<p>We are checking our systems to confirm that we received it. If you haven't sent the money already, please make sure to do so now.</p>" .
					'<p>Once confirmed, we will proceed with the shipping and delivery options you chose.</p>' .
					'<p>Thank you for doing business with us! You will be updated regarding your order details soon.</p>',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'store_instructions'    => array(
					'title'       => 'Store Instructions <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>',
					'type'        => 'textarea',
					'description' => 'Store Instructions that will be added to the thank you page and emails. <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>',
					'default'     => "Please send the total amount requested to our store if you haven't yet",
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'toggleTutorial' => array(
					'title'       => 'Enable Tutorial to display 1min video link',
					'label'       => 'Check to Enable/Uncheck to Disable',
					'type'        => 'checkbox',
					'description' => 'Help your customers checkout with ease by showing this tutorial link',
					'default'     => 'no',
				),
				'toggleCredits' => array(
					'title'       => 'Enable Credits to display Powered by The African Boss',
					'label'       => 'Check to Enable/Uncheck to Disable',
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

			$woocommerce->cart->get_cart();
			$total = $woocommerce->cart->get_total();

			$MOMOApp = sanitize_text_field(trim( 'MOMOApp' ));
			$CustomerMOMOName = sanitize_text_field(trim( 'CustomerMOMOName' ));
			$CustomerMOMONo = sanitize_text_field(trim( 'CustomerMOMONo' ));
			$MOMORefNo = sanitize_text_field(trim( 'MOMORefNo' ));

			echo '<fieldset id="wc-' , esc_attr( $this->id ) , 'form" style="background:white; padding:5%">';

			// Add this action hook if you want your custom payment gateway to support it
			do_action( 'woocommerce_form_start' , $this->id );

			echo '<p>Please select your mobile money payment method to send the ' , $total , '.</p>';

			echo '<p>Use an online mobile money platform (Western Union, Moneygram, WorldRemit) or via a local/online mobile money agent.</p><br>';

			echo '<p>Please fill these fields out below to confirm that you have sent the total requested amount.</p><br>';

			echo '<div class="form-row form-row-wide">

			<label for="' .  esc_attr( $MOMOApp ) . '">Payment Transfer Method used <span class="required">*</span></label>
			<select id="' .  esc_attr( $MOMOApp ) . '" name="' .  esc_attr( $MOMOApp ) . '" style="width:95%; border:1px solid" type="text" autocomplete="off">
				<option value="' , esc_attr( 'empty' ) , '">Please send ' , $total , ' through one of the choices below</option>
				<option value="' , esc_attr( 'MOMO agent' ) , '">- A local/online MOMO Agent to ' , esc_html( wp_kses_post( $this->ReceiverMOMONo ) ) , ' registered under ' , esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ) , '</option>
				<option value="' , esc_attr( 'Western Union' ) , '">- Western Union to ' , esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ) , '</option>
				<option value="' , esc_attr( 'Moneygram' ) , '">- MoneyGram to ' , esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ) , '</option>
				<option value="' , esc_attr( 'Worldremit' ) , '">- World Remit to ' , esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ) , '</option>
			</select>

			</div>

			<div class="form-row form-row-wide">
			<label>Sender Name <span class="required">*</span></label>
			<input id="' . esc_attr( $CustomerMOMOName ) . '" style="text-transform:uppercase; width:95%; border:1px solid" name="' . esc_attr( $CustomerMOMOName ) . '" type="text" placeholder="Insert Full Name" autocomplete="off">
			</div>

			<div class="form-row form-row-wide">
			<label>Sender Phone Number <span class="required">*</span></label>
			<input id="' . esc_attr( $CustomerMOMONo ) . '" name="' . esc_attr( $CustomerMOMONo ) . '" style="width:95%; border:1px solid" type="text" min="111111" size="12" placeholder="+1234567890" autocomplete="off">
			</div>

			<div class="form-row form-row-wide">
			<label>Reference/Transaction Confirmation Code (if applicable)</label>
				<input id="' . esc_attr( $MOMORefNo ) . '" name="' . esc_attr( $MOMORefNo ) . '" style="width:95%; border:1px solid" type="text" placeholder="Insert Confirmation Code" autocomplete="off">
			</div>

			<div class="form-row form-row-wide">
				<label style="width:95%;">Please verify that the Amount Sent is the following: <span class="required">***</span></label>
				<p style="width:95%" class="input-group-text"><span style="margin:0 10px 0 0; font-weight: bold;">' . $total . '</span> <span style="font-size: 0.7em;"><em>(Add any applicable transfer fees)</em></span></p>
			</div>

			<div class="clear"></div>';

			// if MOMO number is provided, we show it
			if ( '' === $this->ReceiverMOMONo ) {
				$call = '';
			} else {
				$call = 'call <a href="tel:' . esc_html( wp_kses_post($this->ReceiverMOMONo)) . '" target="_blank">' . esc_html( wp_kses_post($this->ReceiverMOMONo)) . '</a>.';
			}

			// if email address is provided, we show it
			if ( '' === $this->ReceiverMOMOEmail ) {
				$email = '';
			} else {
				$email = ' you can also email <a href="mailto:' . esc_html( wp_kses_post($this->ReceiverMOMOEmail)) . '" target="_blank">' . esc_html( wp_kses_post($this->ReceiverMOMOEmail)) . '</a>';
			}

			echo 'If you are having an issue, ' , $call , $email , '<br>';

			// if toggle Tutorial is disabled, we do not it
			if ( 'no' === $this->toggleTutorial ) {
				echo '';
			} else {
				echo '<br>See this <a href=' . esc_url("https://theafricanboss.com/momodemo") . ' style="text-decoration: underline" target="_blank">1min video demo</a> explaining how this works.<br>';
			}

			// if toggle Credits is disabled, we do not it
			if ( 'no' === $this->toggleCredits ) {
				echo '';
			} else {
				echo '<br><a href=' . esc_url("https://theafricanboss.com/momo") . ' style="text-decoration: underline;" target="_blank">Powered by The African Boss</a>';
			}

			do_action( 'woocommerce_form_end' , $this->id );

			echo '<div class="clear"></div></fieldset>';
		}

		public function wcmomo_thankyou_page( $order_id ) {
    		if ( 'momo' === $order->get_payment_method() ) {

				$order = wc_get_order( $order_id );
				$total = $order->get_total();

				echo "<h2>MOMO Notice</h2>";
				echo "<p>We are checking our systems to confirm that we received it. If you haven't sent the money already, please make sure to do so now.</p>
				<p>Once confirmed, we will proceed with the shipping and delivery options you chose.</p>
				<p>Thank you for doing business with us! You will be updated regarding your order details soon.</p><br><hr><br>";

			}

		}

		// Add content to the WC emails
		public function wcmomo_instructions_sent( $order, $sent_to_admin, $plain_text = false ) {
			if ( 'on-hold' === $order->get_status() && 'momo' === $order->get_payment_method() ) {

				$MOMOApp = sanitize_text_field(trim($_POST[ 'MOMOApp' ]));
				$CustomerMOMOName = sanitize_text_field(trim($_POST[ 'CustomerMOMOName' ]));
				$CustomerMOMONo = sanitize_text_field(trim($_POST[ 'CustomerMOMONo' ]));
				$MOMORefNo = sanitize_text_field(trim($_POST[ 'MOMORefNo' ]));

				echo '<h2>MOMO Details</h2>';

				echo '<p>We are checking our systems to confirm that we received the total requested amount.</p>' ,
				'<p>In the meantime, here are the details we received from <strong style="text-transform:uppercase;">' .
				esc_html( $CustomerMOMOName ) . '</strong></p> <p>A payment was sent through <strong>' .  esc_html( $MOMOApp ) .
				'</strong> from the following phone number: <strong>' . esc_html( $CustomerMOMONo ) . '</strong></p>' ,
				'<p>Here is the reference code <strong>' . esc_html( $MOMORefNo ) . '</strong></p>' ,
				'<p>Once confirmed, we will proceed with the shipping and delivery options you chose.</p><br><br>';

				echo 'Thank you for doing business with us, <span style="text-transform:uppercase;">' . esc_html( $CustomerMOMOName ) .
				'!</span><br> You will be updated regarding your order details soon<br>';

			}

		}

		/*
		* Payment Custom JS and CSS
		*/
		// public function payment_scripts() {

		// 	// we need JavaScript to process a token only on cart/checkout pages, right?
		// 	if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
		// 		return;
		// 	}

		// 	// if our payment gateway is disabled, we do not have to enqueue JS too
		// 	if ( 'no' === $this->enabled ) {
		// 		return;
		// 	}

		// 	wp_enqueue_script( 'woocommerce_momo' );

		// }

		/*
		* Fields validation
		*/
		public function validate_fields() {
			$customerPaymentMode = sanitize_text_field(trim($_POST[ 'MOMOApp' ]));
			$customerMomoName = sanitize_text_field(trim($_POST[ 'CustomerMOMOName' ]));
			$customerMomoNumber = sanitize_text_field(trim($_POST[ 'CustomerMOMONo' ]));

			if ( $customerPaymentMode == 'empty' ) {
				wc_add_notice(  'Please select a Payment Transfer Method' , 'error' );
			}

			if ( strlen($customerMomoName) < 3 ) {
				wc_add_notice(  'Mobile Money Customer Name ' . esc_html( $customerMomoName ) . ' is too short or empty' , 'error' );
			}

			if ( strlen($customerMomoNumber) < 10 ) {
				wc_add_notice(  'Mobile Money Customer Phone Number ' . esc_html( $customerMomoNumber ) . ' is too short!' , 'error' );
			}

			if ( strlen($customerMomoNumber) > 15 ) {
				wc_add_notice(  'Mobile Money Customer Phone Number ' . esc_html( $customerMomoNumber ) . ' is too long!' , 'error' );
			}

			//$pattern = '/\+?([0-9]{1,3})+-?([0-9]{3,5})+-?([0-9]{3,5})+-?([0-9]{3,5})/s';
			//$pattern = '/\+?([0-9]{2})+-?([0-9]{3})+-?([0-9]{5,10})/s';
			//$pattern = '/\+?[0-9]{10,15}/s';

			if ( preg_match( '/\+?[0-9]{10,15}/s' , $customerMomoNumber) != 1 ) {
				wc_add_notice(  'Mobile Money Customer Phone Number ' . esc_html( $customerMomoNumber ) . ' is invalid! Please submit a number in the format +1234567890 or 1234567890 with 10-15 digits' , 'error' );
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

			$MOMOApp = sanitize_text_field(trim($_POST['MOMOApp']));
			$CustomerMOMOName = sanitize_text_field(trim($_POST['CustomerMOMOName']));
			$CustomerMOMONo = sanitize_text_field(trim($_POST['CustomerMOMONo']));
			$MOMORefNo = sanitize_text_field(trim($_POST['MOMORefNo']));

			if ( !is_wp_error($order) ) {

				// reduce inventory
				$order->reduce_order_stock();

				// Mark as on-hold (we're awaiting the payment).
				$order->update_status( apply_filters( 'wcmomo_process_payment_order_status' , 'on-hold' , $order ), __( 'Checking for payment.<br>' , 'woocommerce' ) );

				if ( 'momo' === $order->get_payment_method() ) {
					$note = '<p>Your order request was received!</p>' .
					'<p>In the meantime, here are the details we received from <strong style="text-transform:uppercase;">' .
					esc_html( $CustomerMOMOName ) . '</strong></p> <p>A payment was sent through <strong>' .
					esc_html( $MOMOApp ) . '</strong> from the following phone number: <strong>' . esc_html( $CustomerMOMONo ) . '</strong></p>' .
					'<p>Here is the reference code <strong>' . esc_html( $MOMORefNo ) . '</strong></p>' .
					'<p>Once confirmed, we will proceed with the shipping and delivery options you chose.</p><br>' .
					'<p>Thank you for doing business with us, <span style="text-transform:uppercase;">' . esc_html( $CustomerMOMOName )  . '!</span></p>' .
					'<p>You will be updated regarding your order details soon.</p>';

					// some notes to customer (replace true with false to make it private)
					$order->add_order_note( $note , true );
					// Send order total to learn more about the impact of the plugin
					wp_mail( 'info@theafricanboss.com', 'Someone used MOMO at checkout', $total );
				}

				// Empty cart
				$woocommerce->cart->empty_cart();

				// Redirect to the thank you page
				return array( 'result' => 'success' , 'redirect' => $this->get_return_url($order) );

			} else {
				wc_add_notice(  'Connection error.', 'error' );
				return false;
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