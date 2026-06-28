<?php

/**
 * Plugin Name: Mashiv Author Coupon Price
 * Description: Allows marking WooCommerce coupons as author coupons that set eligible product prices to 30 NIS when applied.
 * Version:     1.0.1
 * Author:      GreenCode
 * Author URI:  https://greencode.co.il/
 * Text Domain: mashiv-author-coupon-price
 * Domain Path: /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('mashiv_author_coupon_price_init')) {

    function mashiv_author_coupon_price_init()
    {
        if (! class_exists('WooCommerce')) {
            return;
        }

        add_action('woocommerce_coupon_options', 'mashiv_author_coupon_price_add_coupon_field');
        add_action('woocommerce_coupon_options_save', 'mashiv_author_coupon_price_save_coupon_field');
        add_action('woocommerce_before_calculate_totals', 'mashiv_author_coupon_price_apply_fixed_price', 20);
    }

    add_action('plugins_loaded', 'mashiv_author_coupon_price_init', 20);
}

if (! function_exists('mashiv_author_coupon_price_add_coupon_field')) {

    function mashiv_author_coupon_price_add_coupon_field()
    {
        global $post;

        $value = get_post_meta($post->ID, '_mashiv_author_coupon_price_30', true);
        $checked = ('yes' === $value) ? 'yes' : 'no';

        echo '<div class="options_group">';
        woocommerce_wp_checkbox(
            array(
                'id'            => '_mashiv_author_coupon_price_30',
                'label'         => 'קופון סופר - מחיר 30 ₪',
                'description'   => 'כאשר קופון זה מופעל, המוצרים שהוגבלו אליו יקבלו מחיר יחידה של 30 ש"ח.',
                'desc_tip'      => true,
                'value'         => $checked,
            )
        );
        echo '</div>';
    }
}

if (! function_exists('mashiv_author_coupon_price_save_coupon_field')) {

    function mashiv_author_coupon_price_save_coupon_field($post_id)
    {
        $is_enabled = isset($_POST['_mashiv_author_coupon_price_30']) ? 'yes' : 'no';
        update_post_meta($post_id, '_mashiv_author_coupon_price_30', $is_enabled);
    }
}

if (! function_exists('mashiv_author_coupon_price_apply_fixed_price')) {

    function mashiv_author_coupon_price_apply_fixed_price($cart)
    {
        if (is_admin() && ! defined('DOING_AJAX')) {
            return;
        }

        $applied_coupons = $cart->get_applied_coupons();
        if (empty($applied_coupons)) {
            return;
        }

        $fixed_price_coupon_product_ids = array();

        foreach ($applied_coupons as $coupon_code) {
            $coupon = new WC_Coupon($coupon_code);
            $is_fixed_price = $coupon->get_meta('_mashiv_author_coupon_price_30', true);

            if (empty($is_fixed_price) || 'yes' !== $is_fixed_price) {
                continue;
            }

            $product_ids = $coupon->get_product_ids();
            if (! empty($product_ids) && is_array($product_ids)) {
                $fixed_price_coupon_product_ids = array_merge($fixed_price_coupon_product_ids, $product_ids);
            }
        }

        if (empty($fixed_price_coupon_product_ids)) {
            return;
        }

        $fixed_price_coupon_product_ids = array_unique(array_filter($fixed_price_coupon_product_ids));
        if (empty($fixed_price_coupon_product_ids)) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id   = isset($cart_item['product_id']) ? intval($cart_item['product_id']) : 0;
            $variation_id = isset($cart_item['variation_id']) ? intval($cart_item['variation_id']) : 0;

            $matched = false;
            if ($product_id && in_array($product_id, $fixed_price_coupon_product_ids, true)) {
                $matched = true;
            }

            if (! $matched && $variation_id && in_array($variation_id, $fixed_price_coupon_product_ids, true)) {
                $matched = true;
            }

            if ($matched) {
                $cart_item['data']->set_price(30);
            }
        }
    }
}
