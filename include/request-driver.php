<?php
//Create order in Open App // woocommerce_thankyou
add_action( 'woocommerce_order_status_processing', 'ooodd_processing_to_completed');
function ooodd_processing_to_completed($order_id){

    $order  = new WC_Order($order_id);

    $object_order = wc_get_order($order_id);

    foreach( $object_order->get_items( 'shipping' ) as $item_id => $item ){
        $shipping_method_id = $item->get_method_id();
    }

    if($shipping_method_id == 'ooodd_method'){
        if ($GLOBALS["onerequestdriver"] == 1) {

            $payment_title  = $order->get_payment_method_title();
    
            if( $payment_title != 'Direct bank transfer' || $payment_title != 'Check payments' /*|| $payment_title != 'Cash on delivery'*/ ) {
    
                $order->update_status('completed');
    
                $dataPM					= $order->data;
                $date					= $order->get_date_created()->format('Y-m-d');
                $phone					= $dataPM['billing']['phone'];
    
                $opencustomaddress_v    = $GLOBALS["opencustomaddress"];
                $opencustomzipcode_v    = $GLOBALS["opencustomzipcode"];
                $opencustomcity_v       = $GLOBALS["opencustomcity"];
    
                $items_order_count = $order->get_items();
                $products = array();
    
                foreach ($items_order_count as $item_detail) {
    
                    $product_variation_id = $item_detail['variation_id'];
    
                    if ($product_variation_id) { 
                        $product = wc_get_product($item_detail['variation_id']);
                    } else {
                        $product = wc_get_product($item_detail['product_id']);
                    }
    
                    if ($product) { 
                        $item_sku = $product->get_sku();
                        if ($item_sku == '') {
                            $item_sku = $item_detail->get_product_id();
                        }
                    }
                    
                    $description = get_the_excerpt( $item_detail->get_product_id() );
                    if (!$description) {
                        $description = 'No description available';
                    }
    
                    $products[]  = array(
                        'sku'           => $item_sku,
                        'name'          => $item_detail->get_name(),
                        'quantity'      => $item_detail->get_quantity(),
                        'description'   => $description,
                        'price'         => ($item_detail->get_total())/($item_detail->get_quantity())
                    );
                }
    
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
    
                if (!empty($opencustomaddress_v) && !empty($opencustomzipcode_v) && !empty($opencustomcity_v)) {
                    $address = $opencustomaddress_v;
                    $city    = $opencustomcity_v;
                    $zipcode = $opencustomzipcode_v;
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
                    'delivery_subtotal'    => $dataPM['shipping_total'],
                    'delivery_date'         => $date,
                    'food_est_time'         => 25,
                    'dropoff' => array (
                        'merchant_id'       => $GLOBALS["merchant_id"],
                        'contact_name'      => $GLOBALS["contact_name"],
                        'contact_number'    => $GLOBALS["contact_number"],
                        'address'           => $GLOBALS["address"],
                    ),
                    'items' => $products
                );
    
                $args_create_order  = array(
                    'method'        => 'POST',
                    'headers'       => array(
                        'Content-Type'  => 'application/json',
                        'x-app-key'     => $GLOBALS["oneapikey"]
                    ),
                    'body'          => json_encode($body_create_order),
                );
    
                //update_post_meta( $order_id, 'data_request', $body_create_order);
    
                wp_remote_post( $endpoint_create_order, $args_create_order );
            }
        }
    }

}

/*add_action( 'woocommerce_thankyou', 'misha_view_order_and_thankyou_page', 20 );
add_action( 'woocommerce_view_order', 'misha_view_order_and_thankyou_page', 20 );
 
function misha_view_order_and_thankyou_page( $order_id ){
    print_r(get_post_meta( $order_id, 'data_request', true ));
}*/

?>