=== Jigoshop Swipe HQ Payment Gateway Plugin ===
Contributors: Optimizer HQ
Tags: Swipe HQ Checkout, Payment Gateway, Optimizer HQ

Allows you to use Swipe HQ payment gateway with the jigoshop plugin.

== Description ==

This is the Swipe HQ payment gateway for Jigoshop E-commerce. Allows you to use SwipeHQ payment gateway with the Jigoshop plugin. It uses the redirect method, the user is redirected to swipehq so that you don't have to install an SSL certificate on your site.

Visit [http://www.swipehq.com]
== Installation ==
1. Ensure you have the latest version of Jigoshop plugin installed
2. Unzip and upload contents of the plugin to your /wp-content/plugins/ directory
3. Activate the plugin through the 'Plugins' menu in WordPress

To use the SwipeHQ Checkout on your website you need to become a Swipe HQ Checkout merchant.
To set up the extension you need to define 'Merchant ID' and 'API Key'. You can find them on your merchant account under Settings -> API Credentials and copy to the SwipeHQ Checkout extension settings.

== Configuration ==

1. Go to SwipeHQ Checkout Merchant Console
2. Click on the Settings link across the top
3. Click on the "Payment Notifiers" section (on the left)
4. Tick "Pass back user_data parameter".
5. Add the following callback URL: %yoursite%/index.php?swipehq=callback
6. Add the following in the redirection URL:
7. Add the following redirection URL: %yoursite%/index.php?swipehq=redirect

PLEASE NOTE: you need to replace %yoursite% with your website's domain name.

== Changelog ==

= 1.0 =
* First Public Release.