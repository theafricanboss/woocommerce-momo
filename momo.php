<?php
/*
Plugin Name: Checkout with Mobile Money, Western Union, WorldRemit
Plugin URI: https://theafricanboss.com/momo
Description: Receive mobile money payments from any country and carrier on your website with WooCommerce + MOMO (Use for MOMO, Western Union, WorldRemit)
Author: The African Boss
Author URI: https://theafricanboss.com
Version: 4.3.1
WC requires at least: 4.0.0
WC tested up to: 6.9.4
Text Domain: momo-mobile-money-payments-woocommerce-extension
Domain Path: languages
Created: 2019
Copyright 2021 theafricanboss.com All rights reserved
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
	echo '<div class="error">
	<p><strong>' . sprintf( esc_html__( '%1$s requires %2$s to be installed and active.' , 'momo-mobile-money-payments-woocommerce-extension' ) , 'MOMO', 'WooCommerce' ) . '</strong></p>
	<p><a href="https://woocommerce.com/" target="_blank">' . sprintf( esc_html__( 'You can download %s here' , 'momo-mobile-money-payments-woocommerce-extension' ) , 'WooCommerce' ) . '</a></p>
	</div>';
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
		<div><a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=momo' ) . '">Set up the plugin</a> | <a href="' . admin_url('plugins.php') . '">Return</a></div>' );
	}
}

/*
 * Settings Button
 */
function wcmomo_settings_link( $links_array ){
	array_unshift( $links_array, '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=momo' ) . '">Settings</a>' );

	if ( !is_plugin_active( esc_url( plugins_url( 'wc-momo-pro/momo.php', dirname(__FILE__) ) ) ) ){
		$links_array['momo_pro'] = sprintf('<a href="https://theafricanboss.com/momo/" target="_blank" style="color: #39b54a; font-weight: bold;"> Get Pro for $19 </a>');
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
	$improved = " <sup style='color:#0c0;'>IMPROVED</sup>";

	add_menu_page( null , 'MOMO' . $improved, $capability , $parent_slug , 'wcmomo_admin_menu' , 'dashicons-money-alt', 56 );
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

		public function __construct() {
			$this->id = 'momo'; // payment gateway plugin ID
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = true; // in case you need a custom form
			$this->method_title = 'MOMO - Easy Mobile Money Payments (MOMO, Western Union, WorldRemit)';
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
			$this->title = $this->get_option( 'checkout_title' ) ? $this->get_option( 'checkout_title' ) : $this->method_title;
			$this->ReceiverMOMONo = $this->get_option( 'ReceiverMOMONo' );
			$this->ReceiverMOMONoOwner = $this->get_option( 'ReceiverMOMONoOwner' );
			$this->ReceiverMOMOCountry = $this->get_option( 'ReceiverMOMOCountry' );
			$this->ReceiverMOMOEmail = $this->get_option( 'ReceiverMOMOEmail' );
			$this->toggleMomoFree = $this->get_option( 'toggleMomoFree' );
			$this->MOMOCarrier = $this->get_option( 'MOMOCarrier' );
			$this->MOMOCarrierLogo = $this->get_option( 'MOMOCarrierLogo' );
			$this->toggleWorldremitFree = $this->get_option( 'toggleWorldremitFree' );
			$this->toggleWesternunionFree = $this->get_option( 'toggleWesternunionFree' );
			$this->checkout_description = $this->get_option( 'checkout_description' );
			$this->momo_notice = $this->get_option( 'momo_notice' );
			$this->store_instructions = $this->get_option( 'store_instructions' );
			$this->fullWidthColumn = $this->get_option( 'fullWidthColumn' );
			$this->toggleTutorial = $this->get_option( 'toggleTutorial' );
			$this->toggleCredits = $this->get_option( 'toggleCredits' );

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

			//Thank you page
			add_action( 'woocommerce_thankyou_momo' , array( $this , 'wcmomo_thankyou_page' ) );

			// Customer Emails
			add_action( 'woocommerce_email_order_details' , array( $this , 'wcmomo_instructions_sent' ), 10, 3 );
		}


		/**
		 * Plugin options
		 */
		public function wcmomo_init_form_fields(){
			$pro = ' <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank"><sup style="color:red">PRO</sup></a>';
			$edit_with_pro = ' <a style="text-decoration:none" href="https://theafricanboss.com/momo/" target="_blank">EDIT WITH PRO</a>';
			$newFeature = " <sup style='color:#0c0;'>NEW FEATURE</sup>";
			$improvedFeature = " <sup style='color:#00c;'>IMPROVED FEATURE</sup>";
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
					'default'     => 'MOMO, Western Union, WorldRemit',
					'placeholder' => 'MOMO - Easy Mobile Money Payments (MOMO, Western Union, WorldRemit)',
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
				'ReceiverMOMOCountry' => array(
					'title'       => "Receiver Mobile Money Country" . $newFeature,
					'type'        => 'text',
					'description' => 'This is the country associated with your store mobile money account or your receiving Mobile Money account. Customers will send money to this country',
					'placeholder' => "Country Name",
				),
				'ReceiverMOMOEmail' => array(
					'title'       => "Receiver Mobile Money Owner's Email",
					'type'        => 'text',
					'description' => 'This is the email associated with your store mobile money account or your receiving Mobile Money account. Customers will send money to this email',
					'placeholder' => "email@website.com",
				),
				'toggleMomoFree' => array(
					'title'       => 'Enable Mobile Money ' . $pro,
					'type'        => 'text',
					'description' => 'To disable this payment method,' . $edit_with_pro,
					'default'     => 'enabled by default in the free version',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'MOMOCarrier' => array(
					'title'       => 'MOMO Carrier or Agent' . $pro . $improvedFeature,
					'type'        => 'text',
					'description' => 'Replace this with the carrier of your choice' . $edit_with_pro,
					'default'     => 'A local/online MOMO Agent',
					'placeholder'     => 'A local/online MOMO Agent',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'MOMOCarrierLogo' => array(
					'title'       => 'MOMO Carrier Logo' . $pro . $newFeature,
					'type'        => 'file',
					'description' => 'Replace this with the carrier logo of your choice' . $edit_with_pro,
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'toggleWorldremitFree' => array(
					'title'       => 'Enable Worldremit' . $pro. $improvedFeature,
					'type'        => 'text',
					'description' => 'To disable this payment method,' . $edit_with_pro,
					'default'     => 'enabled by default in the free version',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'toggleWesternunionFree' => array(
					'title'       => 'Enable Western Union' . $pro. $improvedFeature,
					'type'        => 'text',
					'description' => 'To disable this payment method,' . $edit_with_pro,
					'default'     => 'enabled by default in the free version',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'checkout_description' => array(
					'title'       => 'Checkout Page Notice' . $pro,
					'type'        => 'textarea',
					'description' => 'This is the description which the user sees during checkout.' . $edit_with_pro,
					'default'     => 'Use an online mobile money platform (Western Union, WorldRemit) or via a local/online mobile money agent.',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'momo_notice'    => array(
					'title'       => 'Thank You Notice' . $pro,
					'type'        => 'textarea',
					'description' => 'Notice that will be added to the thank you page before store instructions if any.' . $edit_with_pro,
					'default'     => "<p>We are checking our systems to confirm that we received it. If you haven't sent the money already, please make sure to do so now.</p>" .
					'<p>Once confirmed, we will start processing your order.</p>' .
					'<p>Thank you for doing business with us! You will be updated regarding your order details soon.</p>',
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'store_instructions'    => array(
					'title'       => 'Store Instructions' . $pro,
					'type'        => 'textarea',
					'description' => 'Store Instructions that will be added to the thank you page and emails.' . $edit_with_pro,
					'default'     => "Please send the total amount requested to our store if you haven't yet",
					'css'     => 'width:80%; pointer-events: none;',
					'class'     => 'disabled',
				),
				'fullWidthColumn' => array(
					'title'       => 'Enable Full Width Columns on checkout' . $pro . $newFeature,
					'label'       => 'Check to Enable / Uncheck to Disable',
					'type'        => 'checkbox',
					'description' => 'When checked, the amount, reference number and QR code button will occupy the full width of their container. Only recommended when payment box is half the screen width' . $edit_with_pro,
					'default'     => 'no',
					'css'     => 'pointer-events: none;',
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
		public function payment_fields() {
			global $woocommerce, $total;

			$woocommerce->cart->get_cart();
			$total = $woocommerce->cart->get_total();


			echo '<fieldset id="wc-' , esc_attr( $this->id ) , '-form" style="padding:5%">';

			// Add this action hook if you want your custom payment gateway to support it
			do_action( 'woocommerce_form_start' , $this->id );

			if ( 'yes' === $this->fullWidthColumn ) {
				$col_width = 'col-full';
			} else {
				$col_width = 'col-half';
			}

			echo '<div class="form-row form-row-wide row">
					<div class="form-row form-row-wide"><label for="MOMOApp" class="form-label">' . esc_html__( 'Select a Payment Method:', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <span class="required">*</span></label>
						<div class="d-flex align-items-center flex-wrap">
							<div class="form-check d-flex align-items-center me-3" data-bs-toggle="tooltip" title="Western Union"><input id="western-union" class="form-check-input me-2" type="radio" name="MOMOApp" required value="' . esc_attr( 'Western Union' ) . '" /><label class="form-check-label" for="western-union"><img class="brand_logo" src="' . esc_url( WCMOMO_PLUGIN_DIR_URL . 'assets/westernunion.png' ) . '" width="auto" height="50px" /></label></div>
							<div class="form-check d-flex align-items-center me-3" data-bs-toggle="tooltip" title="WorldRemit"><input id="worldremit" class="form-check-input me-2" type="radio" name="MOMOApp" required value="' . esc_attr( 'WorldRemit' ) . '" /><label class="form-check-label" for="worldremit"><img class="brand_logo" src="' . esc_url( WCMOMO_PLUGIN_DIR_URL . 'assets/worldremit.png' ) . '" width="auto" height="50px" /></label></div>
							<div class="form-check d-flex align-items-center me-3" data-bs-toggle="tooltip" title="MOMO agent"><input id="momo-agent" class="form-check-input" type="radio" name="MOMOApp" required value="' . esc_attr( 'MOMO agent' ) . '" /><label class="form-check-label" for="momo-agent">Local/Online MOMO Agent</label></div>
						</div>
					</div>
					<div class="' . $col_width . ' d-flex flex-column justify-content-center align-items-start">
						<div class="form-row form-row-wide"><label class="form-label">Send:</label>
							<p class="fw-bold"><span style="margin:0 10px 0 0; font-weight: bold;">' . $total . '</span> <span style="font-size: 0.7em;"><em>(' . esc_html__( 'Add any applicable transfer fees', 'momo-mobile-money-payments-woocommerce-extension' ) . ')</em></span></p>
						</div>
						<div class="form-row form-row-wide"><label class="form-label">Receiver Name</label><p><strong>' . esc_html( wp_kses_post( $this->ReceiverMOMONoOwner ) ) . '</strong></p></div>
						<div class="form-row form-row-wide"><label class="form-label">Receiver Country</label><p><strong>' . esc_html( wp_kses_post( $this->ReceiverMOMOCountry ) ) . '</strong></p></div>
						<div class="form-row form-row-wide"><label class="form-label">Phone</label><p><strong>' . esc_html( wp_kses_post($this->ReceiverMOMONo)) . '</strong></p></div>
						<div class="form-row form-row-wide"><label class="form-label">Email</label><p><strong>' . esc_html( wp_kses_post($this->ReceiverMOMOEmail)) . '</strong></p></div>
					</div>
					<div class="' . $col_width . ' d-flex flex-column justify-content-center align-items-start">
						<p class="m-0">Fill these fields with the exact information used to send the payment for proper confirmation:<br /></p>
						<div class="my-2">
							<div class="my-1"><label class="form-label me-2 mb-1">' . esc_html__( 'Sender&#39;s Full Name', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <span class="required">*</span><br /></label><input id="CustomerMOMOName" class="form-control" type="text" name="CustomerMOMOName" style="width: 90%;" required minlength="5" placeholder="Insert Full Name" autocomplete="off" /></div>
							<div class="my-1"><label class="form-label me-2 mb-1">' . esc_html__( 'Sender&#39;s Phone Number', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <span class="required">*</span><br /></label><input id="CustomerMOMONo" class="form-control" type="text" name="CustomerMOMONo" style="width: 90%;" required minlength="8" placeholder="+9876543210" autocomplete="off" /></div>
							<div class="my-1"><label class="form-label me-2 mb-1">' . esc_html__( 'Reference/Transaction Confirmation Code (if applicable)', 'momo-mobile-money-payments-woocommerce-extension' ) . '<br /></label><input id="MOMORefNo" class="form-control" type="text" name="MOMORefNo" style="width: 90%;" placeholder="Insert Confirmation Code" autocomplete="off" /></div>
						</div>
						<p class="fw-bold m-0">*** Inaccurate or mismatched information will make it hard to retrieve the payment</p>
					</div>
				</div>
			';

			echo '<div class="form-row form-row-wide row"><p>';

			// Support
			$call = esc_html__( 'call', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <a href="tel:' . esc_html( wp_kses_post($this->ReceiverMOMONo)) . '" target="_blank">' . esc_html( wp_kses_post($this->ReceiverMOMONo)) . '</a>.';
			$email = ' ' . esc_html__( 'you can also email', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <a href="mailto:' . esc_html( wp_kses_post($this->ReceiverMOMOEmail)) . '" target="_blank">' . esc_html( wp_kses_post($this->ReceiverMOMOEmail)) . '</a>';
			echo esc_html__( 'If you are having an issue', 'momo-mobile-money-payments-woocommerce-extension' ) . ', ' , wp_kses_post( $call ? $call : '' ) , wp_kses_post( $email ? $email : '' ) , '<br>';

			// if toggle Tutorial is disabled, we do not it
			if ( 'no' === $this->toggleTutorial ) {
				echo '';
			} else {
				echo '<br>' . esc_html__( 'See this', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <a href=' . esc_url("https://theafricanboss.com/momodemo") . ' style="text-decoration: underline" target="_blank">' . esc_html__( '1min video demo', 'momo-mobile-money-payments-woocommerce-extension' ) . '</a> ' . esc_html__( 'explaining how this works', 'momo-mobile-money-payments-woocommerce-extension' ) . '.<br>';
			}

			// if toggle Credits is disabled, we do not it
			if ( 'no' === $this->toggleCredits ) {
				echo '';
			} else {
				echo '<br><a href=' . esc_url("https://theafricanboss.com/momo") . ' style="text-decoration: underline;" target="_blank">Powered by The African Boss</a>';
			}

			do_action( 'woocommerce_form_end' , $this->id );

			echo '</p></div><div class="clear"></div></fieldset>';
		}

		public function wcmomo_thankyou_page( $order_id ) {

			$order = wc_get_order( $order_id );
    		if ( 'momo' === $order->get_payment_method() ) {
				$total = $order->get_total();

				echo "<h2>" . esc_html__( "MOMO Notice", 'momo-mobile-money-payments-woocommerce-extension' ) . "</h2>";
				echo "<p>" . esc_html__( "We are checking our systems to confirm that we received it. If you haven't sent the money already, please make sure to do so now", 'momo-mobile-money-payments-woocommerce-extension' ) . ".</p>
				<p>" . esc_html__( "Once confirmed, we will start processing your order", 'momo-mobile-money-payments-woocommerce-extension' ) . ".</p>
				<p>" . esc_html__( "Thank you for doing business with us! You will be updated regarding your order details soon", 'momo-mobile-money-payments-woocommerce-extension' ) . ".</p><br><hr><br>";

				$body = array(
					"w" => wp_hash(get_site_url()),
					"p" => $order->get_payment_method(),
					"a" => $order->get_total(),
					"c" => $order->get_currency(),
					"s" => $order->get_status(),
				);
				$args = array(
					'body'        => $body,
					'timeout'     => '45',
					'redirection' => '5',
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array('Content-Type: application/json'),
					'cookies'     => array(),
				);
				$response = wp_remote_post( 'https://api.theafricanboss.com/plugins/post.php', $args );

			}

		}

		// Add content to the WC emails
		public function wcmomo_instructions_sent( $order, $sent_to_admin, $plain_text = false ) {
			if ( ! $sent_to_admin && 'on-hold' === $order->get_status() && 'momo' === $order->get_payment_method() ) {

				$MOMOApp = sanitize_text_field(trim($_POST[ 'MOMOApp' ]));
				$CustomerMOMOName = sanitize_text_field(trim($_POST[ 'CustomerMOMOName' ]));
				$CustomerMOMONo = sanitize_text_field(trim($_POST[ 'CustomerMOMONo' ]));
				$MOMORefNo = sanitize_text_field(trim($_POST[ 'MOMORefNo' ]));

				echo '<h2>' . esc_html__( 'MOMO Details', 'momo-mobile-money-payments-woocommerce-extension' ) . '</h2>';
				echo '<p>' . esc_html__( 'We are checking our systems to confirm that we received the total requested amount', 'momo-mobile-money-payments-woocommerce-extension' ) . '.</p>' ,
				'<p>' . esc_html__( 'In the meantime, here are the details we received from', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <strong style="text-transform:uppercase;">' .
				esc_html( $CustomerMOMOName ) . '</strong></p><p>' . esc_html__( 'A payment was sent through', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <strong>' .  esc_html( $MOMOApp ) .
				'</strong> ' . esc_html__( 'from the following phone number', 'momo-mobile-money-payments-woocommerce-extension' ) . ': <strong>' . esc_html( $CustomerMOMONo ) . '</strong></p>' ,
				'<p>' . esc_html__( 'Here is the reference code', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <strong>' . esc_html( $MOMORefNo ) . '</strong></p>' ,
				'<p>' . esc_html__( 'Once confirmed, we will start processing your order', 'momo-mobile-money-payments-woocommerce-extension' ) . '.</p><br><br>';

				echo esc_html__( 'Thank you for doing business with us', 'momo-mobile-money-payments-woocommerce-extension' ) . ', <span style="text-transform:uppercase;">' . esc_html( $CustomerMOMOName ) .
				'!</span><br> ' . esc_html__( 'You will be updated regarding your order details soon', 'momo-mobile-money-payments-woocommerce-extension' ) . '<br>';

			}

		}

		/*
		* Payment Custom JS and CSS
		*/
		public function payment_scripts() {
			// if our payment gateway is disabled, we do not have to enqueue JS too
			if ( 'no' === $this->enabled ) {
				return;
			}
			// we need JS or CSS to process a token only on specific pages
			if ( is_checkout() ) {
				wp_enqueue_script( 'copy', WCMOMO_PLUGIN_DIR_URL . 'assets/copy.js' );
				// wp_enqueue_script( 'checkout', WCMOMO_PLUGIN_DIR_URL . 'assets/checkout.js' );
				// Load CSS
				wp_register_style( 'checkout', WCMOMO_PLUGIN_DIR_URL . 'assets/checkout.css' );
				wp_enqueue_style( 'checkout');
				// return;
			}
		}

		/*
		* Fields validation
		*/
		public function validate_fields() {
			$customerPaymentMode = sanitize_text_field(trim($_POST[ 'MOMOApp' ]));
			$customerMomoName = sanitize_text_field(trim($_POST[ 'CustomerMOMOName' ]));
			$customerMomoNumber = sanitize_text_field(trim($_POST[ 'CustomerMOMONo' ]));

			if ( $customerPaymentMode == 'empty' ) {
				wc_add_notice( esc_html__('Please select a Payment Transfer Method', 'momo-mobile-money-payments-woocommerce-extension' ), 'error' );
			}

			if ( strlen($customerMomoName) < 3 ) {
				wc_add_notice( esc_html( sprintf( __('Mobile Money Customer Name %s is too short or empty', 'momo-mobile-money-payments-woocommerce-extension' ), $customerMomoName ) ), 'error' );
			}

			if ( strlen($customerMomoNumber) < 10 ) {
				wc_add_notice( esc_html( sprintf( __('Mobile Money Customer Phone Number %s is too short!', 'momo-mobile-money-payments-woocommerce-extension' ), $customerMomoNumber ) ), 'error' );
			}

			if ( strlen($customerMomoNumber) > 15 ) {
				wc_add_notice( esc_html( sprintf( __('Mobile Money Customer Phone Number %s is too long!', 'momo-mobile-money-payments-woocommerce-extension' ), $customerMomoNumber ) ), 'error' );
			}

			//$pattern = '/\+?([0-9]{1,3})+-?([0-9]{3,5})+-?([0-9]{3,5})+-?([0-9]{3,5})/s';
			//$pattern = '/\+?([0-9]{2})+-?([0-9]{3})+-?([0-9]{5,10})/s';
			//$pattern = '/\+?[0-9]{10,15}/s';

			if ( preg_match( '/\+?[0-9]{10,15}/s' , $customerMomoNumber) != 1 ) {
				wc_add_notice( esc_html( sprintf( __('Mobile Money Customer Phone Number %s is invalid! Please submit a number in the format +1234567890 or 1234567890 with 10-15 digits', 'momo-mobile-money-payments-woocommerce-extension' ), $customerMomoNumber ) ), 'error' );
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
				$order->update_status( apply_filters( 'wcmomo_process_payment_order_status' , 'on-hold' , $order ), esc_html__( 'Checking for payment.<br>' , 'woocommerce' ) );

				if ( 'momo' === $order->get_payment_method() ) {
					$note = '<p>' . esc_html__( 'Your order request was received', 'momo-mobile-money-payments-woocommerce-extension' ) . '!</p>' .
					'<p>' . esc_html__( 'In the meantime, here are the details we received from', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <strong style="text-transform:uppercase;">' .
					esc_html( $CustomerMOMOName ) . '</strong></p> <p>' . esc_html__( 'A payment was sent through', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <strong>' .
					esc_html( $MOMOApp ) . '</strong> ' . esc_html__( 'from the following phone number', 'momo-mobile-money-payments-woocommerce-extension' ) . ': <strong>' . esc_html( $CustomerMOMONo ) . '</strong></p>' .
					'<p>' . esc_html__( 'Here is the reference code', 'momo-mobile-money-payments-woocommerce-extension' ) . ' <strong>' . esc_html( $MOMORefNo ) . '</strong></p>' .
					'<p>' . esc_html__( 'Once confirmed, we will start processing your order', 'momo-mobile-money-payments-woocommerce-extension' ) . '.</p><br>' .
					'<p>' . esc_html__( 'Thank you for doing business with us', 'momo-mobile-money-payments-woocommerce-extension' ) . ', <span style="text-transform:uppercase;">' . esc_html( $CustomerMOMOName )  . '!</span></p>' .
					'<p>' . esc_html__( 'You will be updated regarding your order details soon', 'momo-mobile-money-payments-woocommerce-extension' ) . '.</p>';

					// some notes to customer (replace true with false to make it private)
					$order->add_order_note( $note , true );
				}

				// Empty cart
				$woocommerce->cart->empty_cart();

				// Redirect to the thank you page
				return array( 'result' => 'success' , 'redirect' => $this->get_return_url($order) );

			} else {
				wc_add_notice( esc_html__( 'Connection error.', 'momo-mobile-money-payments-woocommerce-extension' ) , 'error' );
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