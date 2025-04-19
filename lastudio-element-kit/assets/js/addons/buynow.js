(function ($) {
    'use strict';
    $(function () {
        $('.lakit-core-buynow').on('click', function(e) {
            e.preventDefault();
            const $button = $(this);
            const product_type = $button.data('type');
            const checkout_url = $button.data('checkout');
            let _prefix = '?';
            const $form = $button.closest('form');
            if(checkout_url.includes('?')){
                _prefix = '&';
            }
            if(product_type === 'external'){
                $form.submit();
            }
            else if(product_type === 'grouped'){
                let products_added = false;
                let buy_now_url = checkout_url + _prefix + 'lakit_buynow=yes&' + $form.serialize()
                $form.find('.woocommerce-grouped-product-list-item').each(function() {
                    let $input = $(this).find('input.qty');
                    let quantity = parseInt($input.val());
                    if (quantity > 0) {
                        products_added = true;
                    }
                });
                if (products_added) {
                    window.location.href = checkout_url + _prefix + 'lakit_buynow=yes&' + $form.serialize();
                }
                else {
                    alert(LaStudioKitSettings?.i18n?.cart_group_msg?.replace('&hellip;', '…') ?? 'Please choose the quantity of items you wish to add to your cart…');
                }
            }
            else if(product_type === 'variable'){
                const $variation = $('input[name="variation_id"]', $form);
                let passed = false;
                if($variation){
                    const variation_id = parseFloat($variation.val())
                    if( variation_id > 0 ){
                        passed = true;
                    }
                }
                if(!passed){
                    alert(wc_add_to_cart_variation_params?.i18n_make_a_selection_text ?? 'Please select some product options before adding this product to your cart.');
                }
                else{
                    if( $('.woocommerce-variation-add-to-cart-disabled', $form).length > 0){
                        alert(wc_add_to_cart_variation_params?.i18n_unavailable_text ?? 'Sorry, this product is unavailable. Please choose a different combination.');
                    }
                    else{
                        window.location.href = checkout_url + _prefix + 'lakit_buynow=yes&' + $form.serialize();
                    }
                }
            }
            else{
                window.location.href = checkout_url + _prefix + 'lakit_buynow=yes&' + $form.serialize();
            }
        });
    });
})(jQuery);