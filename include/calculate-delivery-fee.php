<?php
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

function ooodd_shipping_method_init() {
    if ( ! class_exists( 'WC_Shipping_Ooodd' ) ) {
        class WC_Shipping_Ooodd extends WC_Shipping_Method {

            /**
             * Constructor. The instance ID is passed to this.
             */
            public function __construct( $instance_id = 0 ) {
                $this->id                    = 'ooodd_method';
                $this->instance_id           = absint( $instance_id );
                $this->method_title          = __( 'Open One Shipping Method' );
                $this->method_description    = __( 'Open One Shipping method.' );
                $this->supports              = array(
                    'shipping-zones',
                    'instance-settings',
                );
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' 		=> __( 'Enable/Disable' ),
                        'type' 			=> 'checkbox',
                        'label' 		=> __( 'Enable this shipping method' ),
                        'default' 		=> 'yes',
                    ),
                    'title' => array(
                        'title' 		=> __( 'Delivery' ),
                        'type' 			=> 'text',
                        'description' 	=> __( 'This controls the title which the user sees during checkout.' ),
                        'default'		=> __( 'Delivery' ),
                        'desc_tip'		=> true
                    )
                );
                $this->enabled              = $this->get_option( 'enabled' );
                $this->title                = $this->get_option( 'title' );
        
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            /**
             * calculate_shipping function.
             * @param array $package (default: array())
             */
            public function calculate_shipping( $package = array() ) {

                session_start();
                global $woocommerce, $delivery_fee, $payment_method;
                if (is_ajax() && !empty( $_POST['post_data'] ) ) {
                    parse_str( sanitize_post( $_POST['post_data'] ), $post_data );
                } else {
                    $post_data = sanitize_post( $_POST );
                }
            
                $opencustomaddress_v = $GLOBALS["opencustomaddress"];
                $opencustomzipcode_v = $GLOBALS["opencustomzipcode"];
                $opencustomcity_v    = $GLOBALS["opencustomcity"];
                $payment_method      = $post_data['payment_method'];
                    
                if (!empty($post_data['shipping_address_1']) && !empty($post_data['shipping_city']) && !empty($post_data['shipping_state']) && !empty($post_data['shipping_country']) && !empty($post_data['shipping_postcode'])) {
                    $address = $post_data['shipping_address_1'];
                    $city	 = $post_data['shipping_city'];
                    $state	 = $post_data['shipping_state'];
                    $country = $post_data['shipping_country'];
                    $zipcode = $post_data['shipping_postcode'];
                }
                else {
                    $address = $post_data['billing_address_1'];
                    $city    = $post_data['billing_city'];
                    $state   = $post_data['billing_state'];
                    $country = $post_data['billing_country'];
                    $zipcode = $post_data['billing_postcode'];
                }
        
                if (!empty($post_data[$opencustomaddress_v]) && !empty($post_data[$opencustomzipcode_v]) && !empty($post_data[$opencustomcity_v])) {
                    $address = $post_data[$opencustomaddress_v];
                    $city    = $post_data[$opencustomcity_v];
                    $zipcode = $post_data[$opencustomzipcode_v];
                }
        
                $addres_full = $address.', '.$city.' '.$zipcode.', '.$state.', '.$country;
        
                $endpoint_trip_cost = 'https://open1.app/api/retail/'.$GLOBALS["platform_selected"].'/trip_cost/';
        
                $body = array(
                    'address'       => $addres_full,
                    'merchant_id'   => $GLOBALS["merchant_id"]
                );
        
                $args_enable_login = array(
                    'method'	   => 'POST',
                    'headers'	   => array(
                        'Content-Type'  => 'application/json',
                        'x-app-key'     => $GLOBALS["oneapikey"]
                    ),
                    'body'         => json_encode($body),
                );
        
                $response = wp_remote_post( $endpoint_trip_cost, $args_enable_login );
        
                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    echo "Something went wrong: $error_message";
                } else {
                    $prevalue = json_decode($response['body']);
                    $delivery_fee = $prevalue->delivery_cost;
                }

                if ( $GLOBALS["payment_method"] != 'bacs' || $GLOBALS["payment_method"] != 'cheque' /*|| $GLOBALS["payment_method"] != 'cod'*/ ) {
                    $cost_delivery = $GLOBALS["delivery_fee"];
                    $msg = '';
                }

                if ( $GLOBALS["payment_method"] == 'bacs' || $GLOBALS["payment_method"] == 'cheque' /*|| $GLOBALS["payment_method"] == 'cod'*/ ) {
                    $cost_delivery = 0;
                    $msg = '(Payment method not supported for delivery)';
                }

                $rate = array(
                    'id'        => $this->id,
                    'label'     => 'Delivery '.$msg,
                    'cost'      => $cost_delivery,
                    'calc_tax'  => 'per_order'
                );
            
                // Register the rate
                $this->add_rate( $rate );

            }

        }
    }
}

add_action( 'woocommerce_shipping_init', 'ooodd_shipping_method_init' );


add_filter( 'woocommerce_shipping_methods', 'register_ooodd_method' );

function register_ooodd_method( $methods ) {

	// $method contains available shipping methods
	$methods[ 'ooodd_method' ] = 'WC_Shipping_Ooodd';

	return $methods;
}

add_action('woocommerce_checkout_update_order_review', 'checkout_update_refresh_shipping_methods', 10, 1);
function checkout_update_refresh_shipping_methods( $post_data ) {
    $packages = WC()->cart->get_shipping_packages();
    foreach ($packages as $package_key => $package ) {
         WC()->session->set( 'shipping_for_package_' . $package_key, false ); // Or true
    }
}

}