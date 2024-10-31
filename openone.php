<?php
/*
Plugin Name: Open One On Demand Delivery
Plugin URI: https://open1.app/
Description: Basically this plugin is used to link a store developed in woocomerce with the openone api and in this way have a delivery system connected to your online store.
Version: 2.1.3
Author: Zabdiel Maestre
Author URI: https://zabdielmaestre.com
License: GPLv2
*/

add_action( 'admin_enqueue_scripts', 'ooodd_front_scripts' );
function ooodd_front_scripts() {
    wp_register_style( 'openone_styles', plugin_dir_url( __FILE__ ) . 'assets/css/openone.css');
    wp_enqueue_style('openone_styles');
    wp_register_script( 'openone_script', plugins_url('assets/js/openone.js', __FILE__), array('jquery'));
    wp_enqueue_script('openone_script');
}

add_action( 'wp_enqueue_scripts', 'ooodd_admin_scripts' );
function ooodd_admin_scripts() {
    wp_register_script( 'openone_script', plugins_url('assets/js/openone.js', __FILE__), array('jquery'));
    wp_enqueue_script('openone_script');
}

add_action( 'admin_menu', 'ooodd_register_openone_menu' );
function ooodd_register_openone_menu(){
    add_menu_page(
        __( 'Open One Settings', 'textdomain' ),
        'Open One',
        'manage_options',
        'openone_settings',
        'ooodd_admin_layout',
        plugins_url( 'open-one-on-demand-delivery/assets/images/icon.png' ),
        26
    );
}

add_action( 'admin_init',  'ooodd_openone_register_setting' );
function ooodd_openone_register_setting(){
    add_settings_section( 'openone_id_1', 'Fill in all the fields for Open One to work properly.', '', 'openone_slug' );
    add_settings_field( 'openone_apikey', 'Api Key', 'ooodd_print_field_text', 'openone_slug', 'openone_id_1', array( 'openone_apikey' ));
    add_settings_field( 'openone_secretkey', 'Secret Key', 'ooodd_print_field_text', 'openone_slug', 'openone_id_1', array( 'openone_secretkey' ));
    register_setting( 'openone_settings', 'openone_apikey', 'sanitize_text_field' );
    register_setting( 'openone_settings', 'openone_secretkey', 'sanitize_text_field' );

    add_settings_section( 'openone_id_2', 'Activities start time (00:00 - 11:59)', '', 'openone_slug' );
    add_settings_field( 'openone_date_from', 'Start time', 'ooodd_print_field_date_from', 'openone_slug', 'openone_id_2', array( 'openone_date_from' ));
    register_setting( 'openone_settings', 'openone_date_from', 'sanitize_text_field' );

    add_settings_section( 'openone_id_3', 'End of activities schedule (12:00 - 23:59)', '', 'openone_slug' );
    add_settings_field( 'openone_date_to', 'End time', 'ooodd_print_field_date_to', 'openone_slug', 'openone_id_3', array( 'openone_date_to' ));
    register_setting( 'openone_settings', 'openone_date_to', 'sanitize_text_field' );

    add_settings_section( 'openone_id_4', 'Select a store (You must first save your app key and secret key)', '', 'openone_slug' );
    add_settings_field( 'openone_store_selected', 'Select a store', 'ooodd_print_select_store', 'openone_slug', 'openone_id_4', array( 'openone_store_selected' ));
    register_setting( 'openone_settings', 'openone_store_selected', 'sanitize_text_field' );

    add_settings_section( 'openone_id_5', 'Request driver', '', 'openone_slug' );
    add_settings_field( 'openone_check_selected', 'Request driver automatically', 'ooodd_print_check_driver', 'openone_slug', 'openone_id_5', array( 'openone_check_selected' ));
    register_setting( 'openone_settings', 'openone_check_selected', 'sanitize_text_field' );

    add_settings_section( 'openone_id_6', 'Woocommerce Checkout custom fields (If you have a custom form, put here the names of your fields)', '', 'openone_slug' );
    add_settings_field( 'openone_custom_address', 'Name Custom Address Field', 'ooodd_print_field_name_custom', 'openone_slug', 'openone_id_6', array( 'openone_custom_address' ));
    add_settings_field( 'openone_custom_zipcode', 'Name Custom Zipcode Field', 'ooodd_print_field_name_custom', 'openone_slug', 'openone_id_6', array( 'openone_custom_zipcode' ));
    add_settings_field( 'openone_custom_city', 'Name Custom City Field', 'ooodd_print_field_name_custom', 'openone_slug', 'openone_id_6', array( 'openone_custom_city' ));
    register_setting( 'openone_settings', 'openone_custom_address', 'sanitize_text_field' );
    register_setting( 'openone_settings', 'openone_custom_zipcode', 'sanitize_text_field' );
    register_setting( 'openone_settings', 'openone_custom_city', 'sanitize_text_field' );
}

function ooodd_print_field_date_from($args) {
    $option = get_option($args[0]);
    $printfielddatefrom = '<input type="time" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" min="00:00" max="11:59"/>';
    echo $printfielddatefrom;
}

function ooodd_print_field_date_to($args) {
    $option = get_option($args[0]);
    $printfielddateto = '<input type="time" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" min="12:00" max="23:59"/>';
    echo $printfielddateto;
}

function ooodd_print_field_name_custom($args) {
    $option = get_option($args[0]);
    $printfieldname = '<input type="text" class="open-input" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" />';
    echo $printfieldname;
}

function ooodd_print_field_text($args) {
    $option = get_option($args[0]);
    $printfieldtext = '<input type="text" class="open-input" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" />';
    echo $printfieldtext;
}

function ooodd_print_select_store() {
    ?>
        <select name="openone_store_selected">
            <option>Select an option</option>
            <?php
                $options            = '';
                $result_decode      = $GLOBALS["get_all_stores"];

                for ($i=0; $i < count($result_decode); $i++) {

                    $the_name   = $result_decode[$i]->name;
                    $the_key    = $i;

                ?>
                    <option value="<?php echo $the_key; ?>" <?php selected(get_option('openone_store_selected'), $the_key); ?>><?php echo $the_name; ?></option>
                <?php
                }

                echo $options;
            ?>
        </select>
   <?php
}

function ooodd_print_check_driver($args) {
    $option = get_option($args[0]);
    $html = '<input type="checkbox" id="checkbox_openone_admin" name="'. $args[0] .'" value="1"' . checked( 1, $option, false ) . '/>';
    echo $html;
}

function ooodd_admin_layout(){
    include('include/admin-open.php');
}

global $oneapikey, $onesecretkey, $onestoreselected, $onerequestdriver, $starthour, $endhour, $msg_store, $opencustomaddress, $opencustomzipcode, $opencustomcity;
$oneapikey          = get_option('openone_apikey');
$onesecretkey       = get_option('openone_secretkey');
$onestoreselected   = get_option('openone_store_selected');
$onerequestdriver   = get_option('openone_check_selected');
$starthour          = get_option('openone_date_from');
$endhour            = get_option('openone_date_to');
$wordpress_time     = get_option('timezone_string');
$opencustomaddress  = get_option('openone_custom_address'); 
$opencustomzipcode  = get_option('openone_custom_zipcode'); 
$opencustomcity     = get_option('openone_custom_city');
$store_start        = $GLOBALS["starthour"];
$store_end          = $GLOBALS["endhour"];

date_default_timezone_set($wordpress_time);

if( $oneapikey != '' && $onesecretkey != '' ){
    include('include/openapp-data.php');
}

if( $onestoreselected != 'select' && $oneapikey != '' && $onesecretkey != '' ){
    include('include/openone-metabox.php');

    if(time() <= strtotime($store_end) && time() >= strtotime($store_start) ){
        $msg_store = 'NOTE: The delivery service is active while your store is open.';
        include('include/calculate-delivery-fee.php');
        include('include/request-driver.php');
    }
    else {
        $msg_store = 'NOTE: The store is currently closed, the delivery service will not work.';
    }
}
?>