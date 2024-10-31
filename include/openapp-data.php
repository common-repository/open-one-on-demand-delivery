<?php
$endpoint_getdata_openone = 'https://open1.app/api/accounts/application_info/';

$args_getdata_openone = array(
    'method'	   => 'GET',
    'headers'	   => array(
        'Content-Type'  => 'application/json',
        'x-app-key'     => $GLOBALS["oneapikey"]
    )
);

$response = wp_remote_post( $endpoint_getdata_openone, $args_getdata_openone );

if ( is_wp_error( $response ) ) {
    $error_message = $response->get_error_message();
    echo "Something went wrong: $error_message";
} else {
    global $prevalue_getdata_openone, $get_all_stores, $get_data_merchant_selected;

    //Get all data from Open One
    $prevalue_getdata_openone = json_decode($response['body']);

    //Get array of merchants after app key saved
    $get_all_stores = $prevalue_getdata_openone->merchants;

    //Get data for merchant selected in admin page
    $onestoreselected = $GLOBALS["onestoreselected"];

    if($onestoreselected != 'Select an option'){
        $get_data_merchant_selected = $get_all_stores[$onestoreselected];

        global $merchant_id, $contact_name, $contact_number, $address, $platform_selected;
        $merchant_id        = $get_data_merchant_selected->external_id;
        $contact_name       = $get_data_merchant_selected->contact_name;
        $contact_number     = $get_data_merchant_selected->contact_phone_number;
        $address            = $get_data_merchant_selected->address;
        $platform_selected  = $get_data_merchant_selected->platform->code;
    }
}
?>