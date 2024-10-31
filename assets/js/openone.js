jQuery(document).ready(function() {
    jQuery( 'form.checkout' ).on('change', 'input[name="payment_method"]', function() {
        jQuery(document.body).trigger('update_checkout');
    });

    jQuery('#ship-to-different-address-checkbox').on('change', function() {
        jQuery('body').trigger('update_checkout');
        if(!jQuery(this).is(':checked')) {
            jQuery('input[name="shipping_address_1"]').val('')
        }
    });
});
