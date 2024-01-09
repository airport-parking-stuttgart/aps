<?php

/*
 * @package DiscountsManager
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// use $wpdb to exec sql directly
global $wpdb;

$wpdb->query('drop table ' . $wpdb->prefix . 'itweb_products');
$wpdb->query('drop table ' . $wpdb->prefix . 'itweb_orders');
$wpdb->query('drop table ' . $wpdb->prefix . 'itweb_parklots');
$wpdb->query('drop table ' . $wpdb->prefix . 'itweb_events');
$wpdb->query('drop table ' . $wpdb->prefix . 'itweb_prices');