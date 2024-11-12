<?php
/*
    Plugin Name: Softcery Checkout
    Description: A custom checkout enhancement for WooCommerce.
    Version: 1.1
    Author: Kharchuk Nazar
    Author URI: https://nazarwebdeveloper.com/
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('SOFTCERY_CHECKOUT_PATH', plugin_dir_path(__FILE__) . 'woocommerce/');

/**
 * Remove all default checkout fields.
 *
 * @param array $fields
 * @return array
 */
add_filter('woocommerce_checkout_fields', '__return_empty_array');

/**
 * Load custom WooCommerce templates from plugin directory.
 *
 * @param string $template       The located template file.
 * @param string $template_name  Template name requested.
 * @param string $template_path  Template path.
 * @return string
 */
function softcery_load_custom_templates($template, $template_name, $template_path) {
    $custom_template = SOFTCERY_CHECKOUT_PATH . $template_name;

    return file_exists($custom_template) ? $custom_template : $template;
}
add_filter('woocommerce_locate_template', 'softcery_load_custom_templates', 20, 3);

/**
 * Save custom checkout fields into order meta.
 *
 * @param int $order_id
 */
function softcery_save_checkout_fields($order_id) {
    $order = wc_get_order($order_id);

    if ($order) {
        $custom_fields = [
            'sc_billing_first_name' => 'set_billing_first_name',
            'sc_billing_address_1'  => 'set_billing_address_1',
            'sc_billing_email'      => 'set_billing_email'
        ];

        foreach ($custom_fields as $field => $setter_method) {
            if (!empty($_POST[$field])) {
                $order->{$setter_method}(sanitize_text_field($_POST[$field]));
            }
        }

        $order->save();
    }
}
add_action('woocommerce_checkout_update_order_meta', 'softcery_save_checkout_fields');

/**
 * Validate custom checkout fields.
 */
function softcery_validate_checkout_fields() {
    $required_fields = [
        'sc_billing_first_name' => __('Please enter your billing name.', 'woocommerce'),
        'sc_billing_address_1'  => __('Please enter your address.', 'woocommerce'),
        'sc_billing_email'      => __('Please enter your email.', 'woocommerce')
    ];

    foreach ($required_fields as $field => $message) {
        if (empty($_POST[$field])) {
            wc_add_notice($message, 'error');
        }
    }

    if (!empty($_POST['sc_billing_email']) && !filter_var($_POST['sc_billing_email'], FILTER_VALIDATE_EMAIL)) {
        wc_add_notice(__('Invalid email address.', 'woocommerce'), 'error');
    }
}
add_action('woocommerce_checkout_process', 'softcery_validate_checkout_fields');