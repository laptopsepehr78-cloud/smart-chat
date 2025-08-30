<?php
/**
 * Main Plugin Loader Class
 *
 * @package SmartChat
 */

namespace SmartChat;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Loader {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Admin class instance
     */
    private $admin;
    
    /**
     * REST API class instance
     */
    private $rest;
    
    /**
     * Router class instance
     */
    private $router;
    
    /**
     * Rate limiter instance
     */
    private $rate_limiter;
    
    /**
     * Initialize the plugin
     */
    public function init() {
        $this->load_dependencies();
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        require_once SMART_CHAT_PLUGIN_DIR . 'includes/class-admin.php';
        require_once SMART_CHAT_PLUGIN_DIR . 'includes/class-rest.php';
        require_once SMART_CHAT_PLUGIN_DIR . 'includes/class-router.php';
        require_once SMART_CHAT_PLUGIN_DIR . 'includes/class-rate-limit.php';
        require_once SMART_CHAT_PLUGIN_DIR . 'includes/Providers/interface-provider.php';
        require_once SMART_CHAT_PLUGIN_DIR . 'includes/Providers/class-provider-mock.php';
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_footer', array($this, 'render_widget'));
        add_action('init', array($this, 'load_textdomain'));
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        $this->admin = new Admin();
        $this->rest = new REST();
        $this->router = new Router();
        $this->rate_limiter = new Rate_Limit();
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        $options = get_option('smart_chat_options', array());
        
        if (empty($options['enabled'])) {
            return;
        }
        
        wp_enqueue_style(
            'smart-chat-widget',
            SMART_CHAT_PLUGIN_URL . 'assets/css/widget.css',
            array(),
            SMART_CHAT_VERSION
        );
        
        wp_enqueue_script(
            'smart-chat-widget',
            SMART_CHAT_PLUGIN_URL . 'assets/js/widget.js',
            array(),
            SMART_CHAT_VERSION,
            true
        );
        
        wp_localize_script('smart-chat-widget', 'smartChat', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'restUrl' => rest_url('smart-chat/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'options' => $options,
            'isRTL' => is_rtl(),
        ));
    }
    
    /**
     * Render the chat widget
     */
    public function render_widget() {
        $options = get_option('smart_chat_options', array());
        
        if (empty($options['enabled'])) {
            return;
        }
        
        include SMART_CHAT_PLUGIN_DIR . 'templates/widget.php';
    }
    
    /**
     * Load text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'smart-chat',
            false,
            dirname(SMART_CHAT_PLUGIN_BASENAME) . '/languages'
        );
    }
}
