<?php
/**
 * Uninstall script for Smart Chat plugin
 *
 * @package SmartChat
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('smart_chat_options');

// Delete transients
delete_transient('smart_chat_logs');
delete_transient('smart_chat_rate_limit_*');

// Clear scheduled events
wp_clear_scheduled_hook('smart_chat_cleanup_logs');

// Delete any custom database tables if they exist
global $wpdb;

// Example: if you had a custom table
// $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}smart_chat_conversations");

// Clear any cached data
wp_cache_flush();
