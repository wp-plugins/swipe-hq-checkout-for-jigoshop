=== Swipe Checkout Payment Gateway - Jigoshop Extension ===
Contributors: Optimizer Corporation
Tags: Swipe Checkout, Payment Gateway, Optimizer Corporation
Requires at least: 1.7.3
Tested up to: 1.7.3
Stable tag: 2.0

Allows you to use Swipe payment gateway with the Jigoshop plugin for NZ and Canada Merchants.

== Description ==

This is the Swipe HQ payment gateway for Jigoshop E-commerce. Allows you to use SwipeHQ payment gateway with the Jigoshop plugin. It uses the redirect method, the user is redirected to swipehq so that you don't have to install an SSL certificate on your site.

Visit [http://www.swipehq.com]
== Installation ==
1. Ensure you have the latest version of Jigoshop plugin installed
2. Unzip and upload contents of the plugin to your /wp-content/plugins/ directory
3. Activate the plugin through the 'Plugins' menu in WordPress

To use the SwipeHQ Checkout on your website you need to become a Swipe HQ Checkout merchant.
To set up the extension you need to define 'Currency Code', 'Merchant ID', 'API Key', 'Payment Page URL' and 'API URL'. You can find them on your merchant account under Settings -> API Credentials and copy to the SwipeHQ Checkout extension settings.

== Configuration ==

1. Go to SwipeHQ Checkout Merchant Console
2. Click on the Settings link across the top
3. Click on the "Payment Notifiers" section (on the left)
4. Select 'Send them to one of my defined web pages'
5. Enter this on the field provided below: %yoursite%/index.php?swipehq=redirect
6. Tick "Pass back user_data parameter".
7. Enter the following in the Live Payment Notification (LPN) URL field: %yoursite%/index.php?swipehq=callback

PLEASE NOTE: you need to replace %yoursite% with your website's domain name.

== Changelog ==

= 1.0 =
* First Public Release.

= 2.0 =
* Test Mode Compatibility.
* Added Multi-currency Support
* Canadian merchant support
* Minor plugin enhancements