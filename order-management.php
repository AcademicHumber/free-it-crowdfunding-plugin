<?php

/**
 * Redirect to checkout after cart
 */
function redirect_to_checkout($url)
{
    global $product;

    if (!empty($_REQUEST['add-to-cart'])) {
        $product_id = absint($_REQUEST['add-to-cart']);
        $product = wc_get_product($product_id);

        if ($product && $product->is_type('reward')) {

            $checkout_url   = wc_get_checkout_url();
            $preferance     = get_option('wpneo_crowdfunding_add_to_cart_redirect');

            if ($preferance == 'checkout_page') {
                $checkout_url = wc_get_checkout_url();
            } elseif ($preferance == 'cart_page') {
                $checkout_url = wc_get_cart_url();
            } else {
                $checkout_url = get_permalink();
            }

            wc_clear_notices();
            return $checkout_url;
        }
    }
    return $url;
}

add_filter('woocommerce_add_to_cart_redirect',  'redirect_to_checkout'); //Skip cart page after click Donate button, going directly on checkout page


//add_action('woocommerce_checkout_order_processed', 'check_order');

function check_order($order_id)
{
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    wp_die();
}
