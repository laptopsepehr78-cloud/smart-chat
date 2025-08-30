<?php
/**
 * Plugin Name: Smart Chat
 * Plugin URI: https://github.com/hoseinmos/smart-chat
 * Description: افزونه چت هوشمند وردپرس با رابط کاربری مینیمال و پشتیبانی کامل از فارسی/RTL
 * Version: 1.0.0
 * Requires at least: 6.5
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * Author: hoseinmos
 * Author URI: https://github.com/hoseinmos
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smart-chat
 * Domain Path: /languages
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SMART_CHAT_VERSION', '1.0.0');
define('SMART_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMART_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMART_CHAT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load the main plugin class
require_once SMART_CHAT_PLUGIN_DIR . 'includes/class-loader.php';

// Initialize the plugin
function smart_chat_init() {
    $plugin = new SmartChat\Loader();
    $plugin->init();
}
add_action('plugins_loaded', 'smart_chat_init');

// Activation hook
register_activation_hook(__FILE__, 'smart_chat_activate');
function smart_chat_activate() {
    $default_options = array(
        'enabled' => true,
        'position' => 'bottom-right',
        'welcome_message' => 'سلام! اگه سوالی داری من اینجام 🤚',
        'placeholder' => 'پیام خود را بنویسید...',
        'primary_color' => '#007cba',
        'chat_mode' => 'internal',
        'internal_results' => 5,
        'external_weight' => 50,
        'log_enabled' => false,
        'log_retention' => 30,
        'rate_limit' => 10,
    );
    
    add_option('smart_chat_options', $default_options);
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'smart_chat_deactivate');
function smart_chat_deactivate() {
    wp_clear_scheduled_hook('smart_chat_cleanup_logs');
    flush_rewrite_rules();
}
