<?php
/*
Plugin Name: Swipe Checkout Payment Gateway
Plugin URI: http://www.swipehq.com
Description: This plugin extends the Jigoshop payment gateways to add Swipe Payment Gateway for NZ and Canada Merchants.
Version: 2.1
Author: Optimizer Corporation
Author URI: http://www.optimizerhq.com
*/


/*  Copyright 2013  Optimizer Corporation  (email : support@optimizerhq.com) */


/* Add Swipe Checkout Payment Gateway to Jigoshop
------------------------------------------------------------ */

add_action( 'plugins_loaded', 'swipehq_jigoshop_payment_gateway', 0 );
function swipehq_jigoshop_payment_gateway() {
	
	if ( !class_exists( 'jigoshop_payment_gateway' ) ) return; // if the Jigoshop payment gateway class is not available, do nothing
	
		class jigoshop_swipehq_payment_gateway extends jigoshop_payment_gateway {
		
		public function __construct() {
                parent::__construct();          /* installs our gateway options in the settings */
        	$this->id			= 'swipehq_payment_gateway';
        	$this->icon 			= plugins_url( 'checkout-logo.png', __FILE__ );
        	$this->has_fields 		= false;
                $this->payment_url              = trim(get_option( 'jigoshop_payment_url' ).'/');
		
                $this->enabled			= get_option( 'jigoshop_swipehq_enabled' );
                $this->title 			= get_option( 'jigoshop_tgm_swipehq_payment_gateway_title' );
                $this->description 		= get_option( 'jigoshop_tgm_swipehq_payment_gateway_description' );

                add_action( 'jigoshop_update_options', array( &$this, 'process_admin_options' ) );
                add_option( 'jigoshop_swipehq_enabled', 'no' );
                add_option( 'jigoshop_tgm_swipehq_payment_gateway_title', '' );
                add_option( 'jigoshop_tgm_swipehq_payment_gateway_description', '' );
    
    		add_action( 'thankyou_swipehq_payment_gateway', array( &$this, 'thankyou_page' ) );
                add_action('receipt_swipehq_payment_gateway', array(&$this, 'receipt_page'));
                add_action('valid-swipehq-payment-request', array(&$this, 'successful_request'));
                add_action('init', array(&$this, 'check_swipe_response'));
    	} 

    
		/* Construct our function to output and display our gateway
		------------------------------------------------------------ */
		
		public function admin_options() {
                ?>
                <thead><tr><th scope="col" width="200px"><?php echo apply_filters( 'tgm_jigoshop_swipehq_payment_gateway_title', 'Swipe HQ Checkout' ); ?></th><th scope="col" class="desc"><?php echo apply_filters( 'tgm_jigoshop_swipehq_payment_gateway_description', 'SwipeHQ is a hub for integrated payment solutions that enables small businesses in New Zealand to transact smarter, better and cheaper than ever before..' ); ?></th></tr></thead>
    		<tr>
	        	<td class="titledesc"><?php echo apply_filters( 'tgm_jigoshop_enable_swipehq_payment_gateway_title', 'Enable Swipe HQ?' ) ?>:</td>
                        <td class="forminp">
                                <select name="jigoshop_swipehq_enabled" id="jigoshop_swipehq_enabled" style="min-width:100px;">
                                <option value="yes" <?php if ( get_option( 'jigoshop_swipehq_enabled' ) == 'yes' ) echo 'selected="selected"'; ?>><?php _e( 'Yes', 'jigoshop' ); ?></option>
                                <option value="no" <?php if ( get_option( 'jigoshop_swipehq_enabled' ) == 'no' ) echo 'selected="selected"'; ?>><?php _e( 'No', 'jigoshop' ); ?></option>
                                </select>
                        </td>
	    	</tr>
	    	<tr>
	        	<td class="titledesc"><a href="#" tip="<?php echo apply_filters( 'tgm_jigoshop_method_tooltip_description', 'This controls the title which the user sees during checkout.' ); ?>" class="tips" tabindex="99"></a><?php echo apply_filters( 'tgm_jigoshop_method_tooltip_title', 'Method Title' ) ?>:</td>
                        <td class="forminp">
                                <input class="input-text" type="text" name="jigoshop_tgm_swipehq_payment_gateway_title" id="jigoshop_tgm_swipehq_payment_gateway_title" value="<?php if ( $value = get_option( 'jigoshop_tgm_swipehq_payment_gateway_title' ) ) echo $value; else echo 'Swipe HQ Checkout'; ?>" />
                        </td>
	    	</tr>
	    	<tr>
	        	<td class="titledesc"><a href="#" tip="<?php echo apply_filters( 'tgm_jigoshop_message_tooltip_description', 'This controls the description which the user sees during checkout.' ); ?>" class="tips" tabindex="99"></a><?php echo apply_filters( 'tgm_jigoshop_message_tooltip_title', 'Description' ) ?>:</td>
                        <td class="forminp">
                                <input class="input-text wide-input" type="text" name="jigoshop_tgm_swipehq_payment_gateway_description" id="jigoshop_tgm_swipehq_payment_gateway_description" value="<?php if ( $value = get_option( 'jigoshop_tgm_swipehq_payment_gateway_description' ) ) echo $value; ?>" />
                        </td>
	    	</tr>
                <tr>
	        	<td class="titledesc"><a href="#" tip="<?php echo apply_filters( 'tgm_jigoshop_message_tooltip_description', 'List of supported currencies is available at API Credentials under Settings of your Swipe HQ Checkout Admin Page.' ); ?>" class="tips" tabindex="99"></a><?php echo apply_filters( 'tgm_jigoshop_message_tooltip_title', 'Currency Code' ) ?>:</td>
                        <td class="forminp">
                                <input class="input-text" type="text" name="jigoshop_currency" id="jigoshop_currency" value="<?php if ( $value = get_option( 'jigoshop_currency' ) ) echo $value; ?>" />
                        </td>
	    	</tr>
                <tr>
	        	<td class="titledesc"><a href="#" tip="<?php echo apply_filters( 'tgm_jigoshop_message_tooltip_description', 'Merchant ID is available at API Credentials under Settings of your Swipe HQ Checkout Admin Page.' ); ?>" class="tips" tabindex="99"></a><?php echo apply_filters( 'tgm_jigoshop_message_tooltip_title', 'Merchant ID' ) ?>:</td>
                        <td class="forminp">
                                <input class="input-text wide-input" type="text" name="jigoshop_merchant_id" id="jigoshop_merchant_id" value="<?php if ( $value = get_option( 'jigoshop_merchant_id' ) ) echo $value; ?>" />
                        </td>
	    	</tr>
                <tr>
	        	<td class="titledesc"><a href="#" tip="<?php echo apply_filters( 'tgm_jigoshop_message_tooltip_description', 'Given to Merchant by Swipe HQ' ); ?>" class="tips" tabindex="99"></a><?php echo apply_filters( 'tgm_jigoshop_message_tooltip_title', 'API Key' ) ?>:</td>
                        <td class="forminp">
                                <input class="input-text wide-input" type="text" name="jigoshop_api_key" id="jigoshop_api_key" value="<?php if ( $value = get_option( 'jigoshop_api_key' ) ) echo $value; ?>" />
                        </td>
	    	</tr>
                <tr>
	        	<td class="titledesc"><a href="#" tip="<?php echo apply_filters( 'tgm_jigoshop_message_tooltip_description', 'API URL is available at API Credentials under Settings of your Swipe HQ Checkout Admin Page.' ); ?>" class="tips" tabindex="99"></a><?php echo apply_filters( 'tgm_jigoshop_message_tooltip_title', 'API URL' ) ?>:</td>
                        <td class="forminp">
                                <input class="input-text wide-input" type="text" name="jigoshop_api_url" id="jigoshop_api_url" value="<?php if ( $value = get_option( 'jigoshop_api_url' ) ) echo $value; ?>" />
                        </td>
	    	</tr>
                <tr>
	        	<td class="titledesc"><a href="#" tip="<?php echo apply_filters( 'tgm_jigoshop_message_tooltip_description', 'Payment URL is available at API Credentials under Settings of your Swipe HQ Checkout Admin Page.' ); ?>" class="tips" tabindex="99"></a><?php echo apply_filters( 'tgm_jigoshop_message_tooltip_title', 'Payment URL' ) ?>:</td>
                        <td class="forminp">
                                <input class="input-text wide-input" type="text" name="jigoshop_payment_url" id="jigoshop_payment_url" value="<?php if ( $value = get_option( 'jigoshop_payment_url' ) ) echo $value; ?>" />
                        </td>
	    	</tr>
                <?php
                }
    	
    
		/* Display description for payment fields and thank you page
		------------------------------------------------------------ */
		
		function payment_fields() {
			if ( $this->description ) echo wpautop( wptexturize( $this->description ) );
			do_action( 'tgm_jigoshop_payment_fields' ); // allow for insertion of custom code if needed
		}
	
		function thankyou_page() {
                        switch($_REQUEST['result']){
                            case 'accepted':
                            case 'test-accepted':
                                echo wpautop( wptexturize( 'Thank you for shopping with us. Your account has been charged and your transaction is successful. We will be shipping your order to you soon.' ) );
                            break;
                            case 'declined':
                                echo wpautop( wptexturize( '<font color="red">Transaction Declined. We\'re sorry, but the transaction has failed.</font>' ) );
                            break;
                            default:
                                echo wpautop( wptexturize( '<font color="red">Authorization Denied. We\'re sorry, but the transaction has failed.</font>' ) );
                            break;
                        }
                        do_action( 'tgm_jigoshop_thankyou_page' ); // allow for insertion of custom code if needed
		}
		
    
		/* Update options in the database upon save
		------------------------------------------------------------ */
		
                public function process_admin_options() {
                    if( isset( $_POST['jigoshop_swipehq_enabled'] ) ) update_option( 'jigoshop_swipehq_enabled', jigowatt_clean( $_POST['jigoshop_swipehq_enabled'] ) ); else @delete_option( 'jigoshop_swipehq_enabled' );
                    if( isset( $_POST['jigoshop_merchant_id'] ) ) update_option( 'jigoshop_merchant_id', jigowatt_clean( $_POST['jigoshop_merchant_id'] ) ); else @delete_option( 'jigoshop_merchant_id' );
                    if( isset( $_POST['jigoshop_api_key'] ) ) update_option( 'jigoshop_api_key', 	jigowatt_clean( $_POST['jigoshop_api_key'] ) ); else @delete_option( 'jigoshop_api_key' );
                    if( isset( $_POST['jigoshop_tgm_swipehq_payment_gateway_title'] ) ) update_option( 'jigoshop_tgm_swipehq_payment_gateway_title', 	jigowatt_clean( $_POST['jigoshop_tgm_swipehq_payment_gateway_title'] ) ); else @delete_option( 'jigoshop_tgm_swipehq_payment_gateway_title' );
                    if( isset( $_POST['jigoshop_tgm_swipehq_payment_gateway_description'] ) ) update_option( 'jigoshop_tgm_swipehq_payment_gateway_description', 	jigowatt_clean( $_POST['jigoshop_tgm_swipehq_payment_gateway_description'] ) ); else @delete_option( 'jigoshop_tgm_swipehq_payment_gateway_description' );
                    if( isset( $_POST['jigoshop_currency'] ) ) update_option( 'jigoshop_currency', 	jigowatt_clean( $_POST['jigoshop_currency'] ) ); else @delete_option( 'jigoshop_currency' );
                    if( isset( $_POST['jigoshop_api_url'] ) ) update_option( 'jigoshop_api_url', 	jigowatt_clean( $_POST['jigoshop_api_url'] ) ); else @delete_option( 'jigoshop_api_url' );
                    if( isset( $_POST['jigoshop_payment_url'] ) ) update_option( 'jigoshop_payment_url', 	jigowatt_clean( $_POST['jigoshop_payment_url'] ) ); else @delete_option( 'jigoshop_payment_url' );
                }
    	
	
		/* Process order 
		------------------------------------------------------------ */
		
		function process_payment( $order_id ) {
			$order = &new jigoshop_order( $order_id );
                        return array(
                                'result' 	=> 'success',
                                'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(jigoshop_get_page_id('pay'))))
                        );
		
		}

                function receipt_page( $order_id ) {
                    $order = new jigoshop_order( $order_id );
                    $order_id = $order->id;
                    $product_details = '';
                    if (sizeof($order->items)>0) : foreach ($order->items as $item) :
			if(!empty($item['variation_id'])) {
				$_product = new jigoshop_product_variation($item['variation_id']);
			} else {
				$_product = new jigoshop_product($item['id']);
			}

			if ($_product->exists() && $item['qty']) :
				$title = $_product->get_title();
				//if variation, insert variation details into product title
				if ($_product instanceof jigoshop_product_variation) {
					$variation_details = array();

					foreach ($_product->get_variation_attributes() as $name => $value) {
						$variation_details[] = ucfirst(str_replace('tax_', '', $name)) . ': ' . ucfirst($value);
					}

					if (count($variation_details) > 0) {
						$title .= ' (' . implode(', ', $variation_details) . ')';
					}
				}
                                $product_details .= $item['qty'] . ' x ' . $title . '<br/>';
			endif;
                    endforeach; endif;
                    
                    //get product ID using TransactionIdentifier API
                    $items = '';
                    foreach ( $order->items as $item ) {
			$_product = $order->get_product_from_item( $item );
			$items .= $item['qty'] . ' x ' . html_entity_decode(apply_filters('jigoshop_order_product_title', $item['name'], $_product), ENT_QUOTES, 'UTF-8').', ';
                    }
                    $params = array (
                        'merchant_id'           => get_option( 'jigoshop_merchant_id' ),
                        'api_key'               => get_option( 'jigoshop_api_key' ),
                        'td_item'               => trim($items, ', '),
                        'td_description'        => $product_details,
                        'td_amount'             => $order->order_total,
                        'td_default_quantity'   => 1,
                        'td_user_data'          => $order_id,
                        'td_currency'           => strtoupper(trim(get_option( 'jigoshop_currency' )))
                    );
                    
                    $response = $this->post_to_url(trim(get_option( 'jigoshop_api_url' ),'/').'/createTransactionIdentifier.php', $params);
                    $response_data = json_decode($response);
                    if($response_data->response_code == 200 && !empty($response_data->data->identifier)){
                        $trans_id = $response_data->data->identifier;
                        echo '<p>'.__('Thank you for your order, please click the button below to pay with Swipe HQ Checkout.', 'jigoshop').'</p>';
                        echo $this -> generate_swipe_form($trans_id,$order_id);
                    }
                    else{
                        if(isset($response_data->message))
                            echo '<p>'.$response_data->message.'</p>';
                        else
                            echo '<p>'.__('There has been a problem with your order. Please contact your website administrator.', 'jigoshop').'</p>';
                    }

                   
                }

                function generate_swipe_form($trans_id,$order_id){
                    $order = new jigoshop_order( $order_id );
                    return '<form action="'.$this->payment_url.'?identifier_id='.$trans_id.'&checkout=true" method="post" id="swipehq_checkout_form">
				<input type="submit" class="button alt" id="submit_swipehq_checkout_form" value="'.__('Pay via Swipe HQ Checkout', 'jigoshop').'" /> <a class="button cancel" href="'.esc_url($order->get_cancel_order_url()).'">'.__('Cancel order &amp; restore cart', 'jigoshop').'</a>
				<script type="text/javascript">
					jQuery(function(){
						jQuery("body").block(
							{
								message: "<img src=\"'.jigoshop::assets_url().'/assets/images/ajax-loader.gif\" alt=\"Redirecting...\" />'.__('Thank you for your order. We are now redirecting you to Swipe HQ Checkout payment page to make payment.', 'jigoshop').'",
								overlayCSS:
								{
									background: "#fff",
									opacity: 0.6
								},
								css: {
									padding:		20,
									textAlign:	  "center",
									color:		  "#555",
									border:		 "3px solid #aaa",
									backgroundColor:"#fff",
									cursor:		 "wait"
								}
							});
						jQuery("#submit_swipehq_checkout_form").click();
					});
				</script>
			</form>';
                }


                function post_to_url($url, $body) {
                     $ch = curl_init ($url);
                     curl_setopt ($ch, CURLOPT_POST, 1);
                     curl_setopt ($ch, CURLOPT_POSTFIELDS, $body);
                     curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                     $html = curl_exec ($ch);
                     curl_close ($ch);
                     return $html;
                }


                function check_swipe_response(){
                    if(isset($_REQUEST['swipehq'])){
                        $swipehq = $_REQUEST['swipehq'];
                        switch($swipehq){
                            case 'redirect':
                                $order = new jigoshop_order((int) $_REQUEST['user_data'] );
                                $redir = add_query_arg('result',$_REQUEST['result'],add_query_arg( 'key', $order->order_key, add_query_arg( 'order', $order->id, get_permalink( get_option( 'jigoshop_thanks_page_id' ) ) ) ));
                                header( 'Location: '.$redir );
                            break;
                            case 'callback':
                                do_action("valid-swipehq-payment-request", $_REQUEST);
                            break;
                        }
                        exit();
                    }
                }

                function successful_request($posted){
                    if(isset($posted['status']) && isset($posted['identifier_id']) && isset($posted['transaction_id']) && isset($posted['td_user_data'])){
                        $order = new jigoshop_order((int) $posted['td_user_data'] );
                        //Validate Transaction
                        $params = array(
                            'merchant_id'       => get_option( 'jigoshop_merchant_id' ),
                            'api_key'           => get_option( 'jigoshop_api_key' ),
                            'transaction_id'    => $posted['transaction_id'],
                            'identifier_id'     => $posted['identifier_id']

                        );
                        $response = $this->post_to_url(trim(get_option( 'jigoshop_api_url' ),'/').'/verifyTransaction.php', $params);
                        $response_data = json_decode($response);
                        if($response_data->response_code == 200){
                            if(($response_data->data->status == 'accepted' || $response_data->data->status == 'test-accepted') && $response_data->data->transaction_approved == 'yes'){
                                $is_test = ($response_data->data->status == 'test-accepted')? ' test ' : ' ';
                                $order->add_order_note( __('Swipe Checkout'.$is_test.'payment completed. Transaction ID: '.trim($posted['transaction_id']), 'jigoshop') );
                                jigoshop_log( "Swipe HQ Checkout: Swipe Checkout'.$is_test.'payment completed for Order ID: " . $order->id );
                                $order->payment_complete();
                                $order->cart->empty_cart();
                            }
                            else{
                               $order->update_status('on-hold', sprintf(__('Payment %s via Swipe Checkout. Transaction ID: '.trim($posted['transaction_id']), 'jigoshop'), strtolower('Declined') ) );
                               jigoshop_log( "Swipe Checkout: Transaction " . strtolower('Declined') . "for Order ID: " . $order->id );
                            }
                        }
                        else{
                            $order->update_status('cancelled', sprintf(__('Payment %s via Swipe Checkout.', 'jigoshop'), strtolower('Cancelled') ) );
                            jigoshop_log( "Swipe Checkout: Unauthorized Transaction for Order ID: " . $order->id );
                        }
                    }
                }
	
	}


	/* Add our Swipe HQ payment gateway to the Jigoshop gateways
	------------------------------------------------------------ */
 
	add_filter( 'jigoshop_payment_gateways', 'add_swipehq_payment_gateway' );
	function add_swipehq_payment_gateway( $methods ) {
		$methods[] = 'jigoshop_swipehq_payment_gateway'; return $methods;
	}
	
}