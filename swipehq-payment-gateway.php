<?php
/*
Plugin Name: Swipe Checkout for Jigoshop
Plugin URI: http://www.swipehq.com
Description: A payment gateway for Jigoshop
Version: 3.0.0
Author: Swipe
Author URI: http://www.swipehq.com
*/


/*  Copyright 2013  Optimizer Corporation  (email : support@optimizerhq.com) */


/* Add Swipe Checkout Payment Gateway to Jigoshop
------------------------------------------------------------ */

add_action( 'plugins_loaded', 'swipehq_jigoshop', 0 );
add_filter('plugin_action_links', 'swipe_jigoshop_action_links', 10, 2 );

function swipe_jigoshop_action_links( $links, $pluginLink ){
	if($pluginLink != 'swipe-hq-checkout-for-jigoshop/swipehq-payment-gateway.php') return $links;
	$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=jigoshop_settings&tab=payment-gateways#swipe_checkout_for_jigoshop' ) . '">' . __( 'Settings', 'Optimizer' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}


function swipehq_jigoshop() {
	
	if ( !class_exists( 'jigoshop_payment_gateway' ) ) return; // if the Jigoshop payment gateway class is not available, do nothing
	
		class jigoshop_swipehq extends jigoshop_payment_gateway {
		
		public function __construct(){
			parent::__construct();
			$this->id				= 'swipehq';
			$this->icon 			= plugins_url( 'checkout-logo.png', __FILE__ );
			$this->has_fields 		= false;
			
			$this->merchant_id		= Jigoshop_Base::get_options()->get_option('jigoshop_swipehq_merchant_id');
			$this->api_key			= Jigoshop_Base::get_options()->get_option('jigoshop_swipehq_api_key');
			$this->api_url			= trim(Jigoshop_Base::get_options()->get_option('jigoshop_swipehq_api_url'), '/');
			$this->payment_page_url = trim(Jigoshop_Base::get_options()->get_option('jigoshop_swipehq_payment_page_url'), '/');
		
			$this->enabled			= Jigoshop_Base::get_options()->get_option('jigoshop_swipehq_enabled');
			$this->title 			= Jigoshop_Base::get_options()->get_option('jigoshop_swipehq_title');
			$this->description 		= Jigoshop_Base::get_options()->get_option('jigoshop_swipehq_description');

			add_action( 'admin_notices', array( $this, 'swipehq_notices' ) );
			
			add_action('thankyou_swipehq', array( &$this, 'thankyou_page' ) );
			add_action('receipt_swipehq', array(&$this, 'receipt_page'));
			add_action('valid-swipehq-payment-request', array(&$this, 'successful_request'));
			add_action('init', array(&$this, 'check_swipe_response'));
    	} 

    
		/* Construct our function to output and display our gateway
		------------------------------------------------------------ */
		
    	public function get_default_options(){
    		$defaults = array();
    		
    		// Define the Section name for the Jigoshop_Options
    		$defaults[] = array( 
    				'name' => 
    					__('Swipe Checkout', 'jigoshop') . 
    					'<img style="vertical-align:middle;margin-top:-4px;margin-left:10px;" src="'.plugins_url( 'checkout-logo.png', __FILE__ ).'" >'
    				
    				, 
    				'type' => 'title', 
    				'desc' => __('This module allows you to pay with <a href="https://www.swipehq.com">Swipe Checkout</a>.', 'jigoshop'),
    				
    		);
    		
    		// List each option in order of appearance with details
    		$defaults[] = array(
    				'name'		=> __('Enable Swipe Checkout','jigoshop'),
    				'desc' 		=> '',
    				'tip' 		=> '',
    				'id' 		=> 'jigoshop_swipehq_enabled',
    				'std' 		=> 'yes',
    				'type' 		=> 'checkbox',
    				'choices'	=> array(
    						'no'			=> __('No', 'jigoshop'),
    						'yes'			=> __('Yes', 'jigoshop')
    				)
    		);
    		
    		$defaults[] = array(
    				'name'		=> __('Method Title','jigoshop'),
    				'desc' 		=> '',
    				'tip' 		=> __('This controls the title which the user sees during checkout.','jigoshop'),
    				'id' 		=> 'jigoshop_swipehq_title',
    				'std' 		=> __('Swipe Checkout','jigoshop'),
    				'type' 		=> 'text'
    		);
    		$defaults[] = array(
    				'name'		=> __('Customer Message','jigoshop'),
    				'desc' 		=> '',
    				'tip' 		=> __('This controls the description which the user sees during checkout.','jigoshop'),
    				'id' 		=> 'jigoshop_swipehq_description',
    				'std' 		=> __('Pay with Swipe Checkout', 'jigoshop'),
    				'type' 		=> 'longtext'
    		);
    		$defaults[] = array(
    				'name'		=> __('Merchant ID','jigoshop'),
    				'desc' 		=> 'Find this in your Swipe Merchant login under Settings -> API Credentials',
    				'tip' 		=> '',
    				'id' 		=> 'jigoshop_swipehq_merchant_id',
    				'std' 		=> '',
    				'type' 		=> 'text'
    		);
    		$defaults[] = array(
    				'name'		=> __('API Key','jigoshop'),
    				'desc' 		=> 'Find this in your Swipe Merchant login under Settings -> API Credentials',
    				'tip' 		=> '',
    				'id' 		=> 'jigoshop_swipehq_api_key',
    				'std' 		=> '',
    				'type' 		=> 'longtext'
    		);
    		$defaults[] = array(
    				'name'		=> __('API Url','jigoshop'),
    				'desc' 		=> 'Find this in your Swipe Merchant login under Settings -> API Credentials',
    				'tip' 		=> '',
    				'id' 		=> 'jigoshop_swipehq_api_url',
    				'std' 		=> '',
    				'type' 		=> 'longtext'
    		);
    		$defaults[] = array(
    				'name'		=> __('Payment Page Url','jigoshop'),
    				'desc' 		=> 'Find this in your Swipe Merchant login under Settings -> API Credentials',
    				'tip' 		=> '',
    				'id' 		=> 'jigoshop_swipehq_payment_page_url',
    				'std' 		=> '',
    				'type' 		=> 'longtext'
    		);
    		
    		return $defaults;
    	}
    	
    	public function is_available() {
    		if ( $this->enabled != 'yes' ) {
    			return false;
    		}
    		$currency = Jigoshop_Base::get_options()->get_option( 'jigoshop_currency' );
    		$acceptedCurrencies = $this->getAcceptedCurrencies();
    		if($acceptedCurrencies !== null && !in_array($currency, $acceptedCurrencies)){
    			return false;
    		}
    		return true;
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
                        'merchant_id'           => $this->merchant_id,
                        'api_key'               => $this->api_key,
                        'td_item'               => trim($items, ', '),
                        'td_description'        => $product_details,
                        'td_amount'             => $order->order_total,
                        'td_default_quantity'   => 1,
                        'td_user_data'          => $order_id,
                        'td_currency'           => strtoupper(trim(get_option( 'jigoshop_currency' )))
                    );
                    
                    $response = $this->post_to_url($this->api_url.'/createTransactionIdentifier.php', $params);
                    $response_data = json_decode($response);
                    if($response_data->response_code == 200 && !empty($response_data->data->identifier)){
                        $trans_id = $response_data->data->identifier;
                        echo '<p>'.__('Thank you for your order, please click the button below to pay with Swipe HQ Checkout.', 'jigoshop').'</p>';
                        echo $this -> generate_swipehq_form($trans_id,$order_id);
                    }
                    else{
                        if(isset($response_data->message))
                            echo '<p>'.$response_data->message.'</p>';
                        else
                            echo '<p>'.__('There has been a problem with your order. Please contact your website administrator.', 'jigoshop').'</p>';
                    }

                   
                }

                function generate_swipehq_form($trans_id,$order_id){
                    $order = new jigoshop_order( $order_id );
                    return '<form action="'.$this->payment_page_url.'?identifier_id='.$trans_id.'&checkout=true" method="post" id="swipehq_checkout_form">
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
                            'merchant_id'       => $this->merchant_id,
                            'api_key'           => $this->api_key,
                            'transaction_id'    => $posted['transaction_id'],
                            'identifier_id'     => $posted['identifier_id']

                        );
                        $response = $this->post_to_url($this->api_url.'/verifyTransaction.php', $params);
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
                
                
                /**
                 *  Admin Notices for conditions under which this plugin is available on a Shop
                 */
                public function swipehq_notices() {
                	 
                	if ( $this->enabled == 'no' ) return;
                
                	$currency = Jigoshop_Base::get_options()->get_option( 'jigoshop_currency' );
                	$acceptedCurrencies = $this->getAcceptedCurrencies();
                	
                	if ($acceptedCurrencies !== null && !in_array($currency, $acceptedCurrencies)) {
                		echo '<div class="error"><p>'.__('Swipe Checkout does not support the currency your Jigoshop is in: '.$currency.'. Swipe Checkout supports these currencies: '.join(', ', $acceptedCurrencies).'.','jigoshop').'</p></div>';
                		Jigoshop_Base::get_options()->set_option( 'jigoshop_swipehq_enabled', 'no' );
                	}
           
                }
                
                protected function getAcceptedCurrencies(){
                	if($this->api_url && $this->api_key && $this->merchant_id){
                		$response = $this->post_to_url($this->api_url . '/fetchCurrencyCodes.php', array(
                			'merchant_id' => $this->merchant_id,
                			'api_key' => $this->api_key	
                		));
                		$responseArr = json_decode($response, true);
                		return $responseArr['data'];
                	}else{
                		return null;
                	}
                }
	
	}


	/* Add our Swipe HQ payment gateway to the Jigoshop gateways
	------------------------------------------------------------ */
 
	add_filter( 'jigoshop_payment_gateways', 'add_swipehq' );
	function add_swipehq( $methods ) {
		$methods[] = 'jigoshop_swipehq'; return $methods;
	}
	
}