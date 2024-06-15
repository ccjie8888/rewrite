<?php
/*
Plugin Name: Custom URL Rewrite
Description: Rewrites URLs to use clean paths for product attributes while keeping the short URL visible.
Version: 1.3
Author: Your Name
*/

function custom_product_rewrite_rules()
{
    add_rewrite_rule(
        '^shop/([^/]+)/([^/]+)/([^/]+)/([^/]+)/?$', // Match four path segments
        'index.php?custom_product_path=$matches[1]/$matches[2]/$matches[3]&attribute_pa_color=$matches[4]', // Rewrite target
        'top' // Priority of the rewrite rule
    );
}
add_action('init', 'custom_product_rewrite_rules');

function add_query_vars_filter($vars)
{
    $vars[] = 'custom_product_path';
    $vars[] = 'attribute_pa_color';
    return $vars;
}
add_filter('query_vars', 'add_query_vars_filter');

function custom_template_redirect()
{
    global $wp_query;

    if (isset($wp_query->query_vars['custom_product_path']) && isset($wp_query->query_vars['attribute_pa_color'])) {
        $path = $wp_query->query_vars['custom_product_path'];
        $color = $wp_query->query_vars['attribute_pa_color'];

        // Build the target URL
        $target_url = home_url('/shop/' . $path . '?attribute_pa_color=' . $color);

        // Fetch the target URL's content
        $response = wp_remote_get($target_url);

        if (is_wp_error($response)) {
            return;
        }

        $body = wp_remote_retrieve_body($response);

        // Output the target URL's content
        echo $body;
        exit();
    }
}
add_action('template_redirect', 'custom_template_redirect');

function custom_product_ajax_handler()
{
    $path = sanitize_text_field($_POST['custom_product_path']);
    $color = sanitize_text_field($_POST['attribute_pa_color']);

    // Build the target URL
    $target_url = home_url('/shop/' . $path . '?attribute_pa_color=' . $color);

    // Fetch the target URL's content
    $response = wp_remote_get($target_url);

    if (is_wp_error($response)) {
        wp_send_json_error('Error retrieving content.');
    }

    $body = wp_remote_retrieve_body($response);

    // Return the target URL's content and the new URL
    wp_send_json_success(array(
        'content' => $body,
        'url' => home_url('/shop/' . $path . '/' . $color . '/')
    ));
}

add_action('wp_ajax_nopriv_load_product_variation', 'custom_product_ajax_handler');
add_action('wp_ajax_load_product_variation', 'custom_product_ajax_handler');

function enqueue_custom_scripts()
{
    wp_enqueue_script('custom-product-ajax', plugin_dir_url(__FILE__) . 'js/custom-product-ajax.js', array('jquery'), null, true);
    wp_localize_script('custom-product-ajax', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'siteurl' => home_url()
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');
