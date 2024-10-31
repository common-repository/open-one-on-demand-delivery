<?php
// Add a custom metabox only for shop_order post type (order edit pages)
add_action( 'add_meta_boxes', 'ooodd_add_meta_boxesws' );
function ooodd_add_meta_boxesws()
{
    add_meta_box( 'custom_order_meta_box', __( 'Open One' ), 'ooodd_custom_metabox_content', 'shop_order', 'side', 'high');
}

function ooodd_custom_metabox_content(){

    $order_id = sanitize_text_field( $_GET['post'] );

    $object_order = wc_get_order($order_id);

    foreach( $object_order->get_items( 'shipping' ) as $item_id => $item ){
        $shipping_method_id = $item->get_method_id();
    }

    if($shipping_method_id != 'ooodd_method'){
        ?>
            <p><?php _e( 'This order was not sent through Open One.' ); ?></p>
        <?php
    }
    else {
        $order                  = new WC_Order($order_id);
        $order_data             = $order->get_data();
        $order_id               = $order_data['id'];
        $order_status           = $order_data['status'];
    
        if($order_status != 'completed'){
            ?>
                <p id="response_ajax_driver"><?php _e( 'You have not requested a driver for this order.' ); ?></p>
                <a href="?post=<?php echo $order_id; ?>&action=edit&driver=true" class="button"><?php _e('Request Driver'); ?></a>
            <?php
            function ooodd_request_driver(){
                $order_id = sanitize_text_field( $_GET['post'] );
    
                $order               = new WC_Order($order_id);
                $order->update_status('completed');
                $dataPM              = $order->get_data();
                $date                = $order->get_date_created()->format('Y-m-d');
                $phone               = $dataPM['billing']['phone'];
                $opencustomaddress_v = $GLOBALS["opencustomaddress"];
                $opencustomzipcode_v = $GLOBALS["opencustomzipcode"];
                $opencustomcity_v    = $GLOBALS["opencustomcity"];
    
                if (!empty($dataPM['shipping']['address_1']) && !empty($dataPM['shipping']['state']) && !empty($dataPM['shipping']['city']) && !empty($dataPM['shipping']['country']) && !empty($dataPM['shipping']['postcode']) && !empty($dataPM['shipping']['first_name']) && !empty($dataPM['shipping']['last_name'])) {
                    
                    $name    = $dataPM['shipping']['first_name'] .' '. $dataPM['shipping']['last_name'];
                    $address = $dataPM['shipping']['address_1'];
                    $city	 = $dataPM['shipping']['city'];
                    $state	 = $dataPM['shipping']['state'];
                    $country = $dataPM['shipping']['country'];
                    $zipcode = $dataPM['shipping']['postcode'];
                }
                else {
                    $name    = $dataPM['billing']['first_name'] .' '. $dataPM['billing']['last_name'];
                    $address = $dataPM['billing']['address_1'];
                    $city	 = $dataPM['billing']['city'];
                    $state	 = $dataPM['billing']['state'];
                    $country = $dataPM['billing']['country'];
                    $zipcode = $dataPM['billing']['postcode'];
                }
    
                if (!empty($dataPM[$opencustomaddress_v]) && !empty($dataPM[$opencustomzipcode_v]) && !dataPM($post_data[$opencustomcity_v])) {
                    $address = $dataPM[$opencustomaddress_v];
                    $city    = $dataPM[$opencustomcity_v];
                    $zipcode = $dataPM[$opencustomzipcode_v];
                }
    
                $addres_full = $address.', '.$city.' '.$zipcode.', '.$state.', '.$country;
    
                $endpoint_create_order	= 'https://open1.app/api/retail/'.$GLOBALS["platform_selected"].'/order/';
    
                $body_create_order  = array(
                    'secret'        => $GLOBALS["onesecretkey"],
                    'merchant_id'   => $GLOBALS["merchant_id"],
                    'customer'  => array (
                        'name'      => $name,
                        'phone'     => $phone,
                        'address'   => $addres_full,
                    ),
                    'delivery_date'     => $date,
                    'delivery_subtotal' => $GLOBALS["delivery_fee"],
                    'dropoff' => array (
                        'merchant_id'       => $GLOBALS["merchant_id"],
                        'contact_name'      => $GLOBALS["contact_name"],
                        'contact_number'    => $GLOBALS["contact_number"],
                        'address'           => $GLOBALS["address"],
                    ),
                );
    
                $args_create_order  = array(
                    'method'        => 'POST',
                    'headers'       => array(
                        'Content-Type'  => 'application/json',
                        'x-app-key'     => $GLOBALS["oneapikey"]
                    ),
                    'body'          => json_encode($body_create_order),
                );
    
                wp_remote_post( $endpoint_create_order, $args_create_order );
    
                header("Refresh:0");
    
            }
            if ( isset( $_GET['driver'] ) && ! empty( $_GET['driver'] ) ) {
                ooodd_request_driver();
            }
        }
        else {
            _e( '<p>This order is completed, therefore, a shipping request has already been generated.</p>' );
        }
    }
    
}
?>