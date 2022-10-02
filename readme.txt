=== Checkout with Mobile Money, Western Union, WorldRemit, WorldRemit ===
Contributors: theafricanboss
Donate Link: https://theafricanboss.com
Tags: momo, woocommerce, mobile money, money transfer, western union, world remit, mtn, vodacom
Requires at least: 4.0
Tested up to: 6.0.2
Stable tag: 4.3.1
Requires PHP: 5.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Receive mobile money, Western Union, WorldRemit payments from any country and carrier on your website with WooCommerce + MOMO

== Description ==

**Now compatible with Translation plugins (like Loco, WPML, etc) meaning you can translate the Checkout, Thank you page and Email notices**
**You can also add the carrier name of your mobile money account and display/hide the payment methods you accept in the PRO version**

For more details about this woocommerce extension, **please visit [The African Boss](https://theafricanboss.com/momo)**

See available screenshots or the store example of [Gura Stores](https://gurastores.com/test/) for visual details.

= PRO or customized version =

Please reach out to theafricanboss@gmail.com for a customized version of this plugin or for the pro version.

You may get upgrades of ‘MOMO PRO’ from [The African Boss](https://theafricanboss.com/momo)

Upgrades include **adding multiple numbers, different carriers, and more**

= Demo =

An example of the plugin in use is the following store:

[Gura Stores](https://gurastores.com/test/)


= Compatible or Available Countries =

This plugin is compatible with **any carrier in any country** since all it does is report details of a mobile money transaction between a customer and a store owner.

See the screenshots or the store example of [Gura Stores](https://gurastores.com/test/) for visual details.


== Installation ==

= From Dashboard ( WordPress admin ) =

* Go to Plugins -> Add New
* Search for ‘Mobile Money, Western Union, WorldRemit’
* Click on Install Now
* Activate the plugin through the “Plugins” menu in WordPress.

= Using cPanel or FTP =

* Download ‘Mobile Money, Western Union, WorldRemit’ from [WordPress](https://wordpress.org/plugins/momo-mobile-money-payments-woocommerce-extension/)
* Unzip the downloaded ZIP file and
* Upload the unzipped folder to the “/wp-content/plugins/” directory.
* Activate the plugin through the “Plugins” menu in WordPress.

= After Plugin Activation =

Find MOMO in your admin dashboard left sidebar menu of buttons

**or**

Go to Woocommerce-> Settings-> Payments screen to configure the plugin


Also *you can visit* the [Official Documentation](https://github.com/theafricanboss/woocommerce-momo) page for further setup instructions.


== Frequently Asked Questions ==

= Does MOMO integrate Payment APIs? =

MOMO plugin is a quick and easy way to notify the store owner that mobile money has been sent their way.
Unfortunately, it doesn't integrate any APIs and only notifies the store owner and the customer that the offline mobile money transaction took place.
Please check screenshots for more details on what is reported.


== Screenshots ==

1. This is what the customer visiting your website will see at the checkout page
2. This is what you will submit when setting up the plugin and this information will be displayed to your customers


== Changelog ==

= 4.3.1 October 1, 2022 =
- disabled .payment_method_momo input selector width
- removed fieldset white background
- Updated Woocommerce and Wordpress compatibility

= 4.3 August 15, 2022 =
- Updated Checkout page UI to show clearer sender and receiver information
- Added MOMO country field, other brand logo and width class field for admin
- Added payment brand logos with select option
- Added receiver information to the checkout UI
- Updated translation domain to permalink
- Removed Moneygram
- Updated Woocommerce and Wordpress compatibility

= 4.2 December 5, 2021 =
- Updated from woocommerce_before_thankyou to woocommerce_thankyou_payment-method-id for compatibility with thank you page customizer plugins
- Moved menu order to below woocommerce menu - position 56
- Fixed error bug that disallowed upgrade/downgrade due to global constants structure in free MOMO<PAYMENT>PRO_ while in paid, MOMO<PAYMENT>_PRO_
- Fixed admin_url functions with issues
- Added ! $sent_to_admin / $sent_to_admin = false to email instructions
- Replaced woocommerce_email_before_order_table hook by woocommerce_email_order_details
- Updated Woocommerce and Wordpress compatibility

= 4.1 September 7, 2021 =
- Changed the wording and removed "shipping and delivery" to include digital woocommerce sellers
- Removed version date

= 4.0.1 August 30, 2021 =
- Fixed order order_id occurences

= 4.0 August 27, 2021 =
- Renamed MOMO to Checkout with Mobile Money, Western Union, WorldRemit
- Edit your own MOMO Carrier/Agent name for your customers
- Toggle or Display/Hide payment options in PRO
- Removed cashapp and added WorldRemit
- Sharing payment methods with free versions to keep data across
- Fixed 'if functions for on-hold and check payment methods' placement
- Improved deactivate free plugins when PRO activated
Smooth upgrade from free to PRO
- PRO invitation admin notice when using free plugin
- Better settings links on plugins page
- Removed review notice asking for reviews
- Better installation instructions
- renamed PRO versions to [payment_name PRO]
- Added free and paid recommended menus in sidebar with colors
- Fixed menu buttons in PRO plugin
- Updated WP and WC compatibility

= 3.0.8 Mar 24, 2021 =
* Cashapp option no longer supported. Moved to our standalone Cashapp plugin
* Send specific notices while awaiting payment with $order->get_status() &&  $order->get_payment_method()
* Updated WP and WC compatibility

= 3.0.7 Mar 24, 2021 =
* Added admin pages: recommended plugins and tutorials
* Added recommended plugins button next to deactivate button
* Updated price for PRO to $19
* Updated the readme file with reason of recent low rating for transparency
* Updated compatible WordPress and WooCommerce versions

= 3.0.6 Feb 9, 2021 =
* Fixed a bug. A line of code was missing include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
* Very sorry for the incovenience
* Updated the readme file with reason of recent low rating for transparency
* Updated compatible WordPress and WooCommerce versions

= 3.0.5 Jan 14, 2021 =
* Compatible with WP v5.6

= 3.0.4 Dec 1, 2020 =
* Updated Payment Transfer Method used text
* Updated some plugin functions for more compatibility
* Updated file directions for more wordpress compatibility
* Updated fields in MOMO setings
* Updated plugin to work in sync with the pro version
* Removed pro fields
* Fixed order_id function where the order details were not being accessed correctly
* Removed Upgrade notices
* Started adding dates to Changelog

= 3.0.3 Aug 1, 2020 =
* Added 2 buttons to admin dashboard: one to invite reviews, and another for our other plugins
* Updated admin dashicon to money icon
* Now allowing PHP versions 5.0 and higher

= 3.0.1 Jun 1, 2020 =
* Updated validation fields detailing all validation errors such as phone number too short, too long, invalid, etc
* Admins with a PRO version can edit the text on top of the MOMO checkout form
* Admins with a PRO version can edit notices on the thank you page, and customer emails
* Admins with a PRO version can add additional store instructions
* Without the PRO account, the default will be applied
* Order status changes to on-hold instead of pending payment

= 3.0.0 May 1, 2020 =
* Updated select fields that needed spacing
* Edited the note sent to customers in the email also the same note is added under orders in wp-admin
* Added a field for additional store instructions that the store owner may use to add any instructions or any additional stuff they need
* Store instructions will be displayed on the thank you page and will also be sent in email to customer

= 2.6.0 =
* Removed input field for transferred money
* Added total order amount instead
* Removed ApplePay, GooglePay, and Venmo payment methods and kept MOMO, Western Union and WorldRemit

= 2.5.0 =
* Validation of input fields
* Sanitization of input fields

= 2.4.5 =
* Fixed Settings Link
* Added Dashboard Menu MOMO Button

= 2.4.0 =
* Settings default/placeholder updated.
* Contact number replaced by Email in the support section of the checkout form.
* Customer note wording updated. Better personalized note.
* Send feedback note commented in

= 2.3.0 =
* Added contact info from the backend to the dropdown

== Upgrade for more ==

You may get upgrades of ‘MOMO PRO’ from [The African Boss](https://theafricanboss.com/momo)

Upgrades include **adding multiple numbers, different carriers, and more**


<?php code();?>