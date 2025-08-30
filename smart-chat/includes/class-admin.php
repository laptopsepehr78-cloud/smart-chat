<?php
/**
 * Admin Settings Class
 *
 * @package SmartChat
 */

namespace SmartChat;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Admin {
    
    /**
     * Initialize admin functionality
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Smart Chat Settings', 'smart-chat'),
            __('Smart Chat', 'smart-chat'),
            'manage_options',
            'smart-chat',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('smart_chat_options', 'smart_chat_options', array(
            'sanitize_callback' => array($this, 'sanitize_options')
        ));
        
        // General Settings
        add_settings_section(
            'smart_chat_general',
            __('تنظیمات عمومی', 'smart-chat'),
            array($this, 'render_general_section'),
            'smart-chat'
        );
        
        add_settings_field(
            'enabled',
            __('فعال', 'smart-chat'),
            array($this, 'render_checkbox_field'),
            'smart-chat',
            'smart_chat_general',
            array('field' => 'enabled')
        );
        
        add_settings_field(
            'position',
            __('جایگاه آیکن', 'smart-chat'),
            array($this, 'render_select_field'),
            'smart-chat',
            'smart_chat_general',
            array(
                'field' => 'position',
                'options' => array(
                    'bottom-right' => __('پایین راست', 'smart-chat'),
                    'bottom-left' => __('پایین چپ', 'smart-chat'),
                    'top-right' => __('بالا راست', 'smart-chat'),
                    'top-left' => __('بالا چپ', 'smart-chat')
                )
            )
        );
        
        add_settings_field(
            'welcome_message',
            __('پیام خوشامد', 'smart-chat'),
            array($this, 'render_textarea_field'),
            'smart-chat',
            'smart_chat_general',
            array('field' => 'welcome_message')
        );
        
        add_settings_field(
            'placeholder',
            __('متن placeholder', 'smart-chat'),
            array($this, 'render_text_field'),
            'smart-chat',
            'smart_chat_general',
            array('field' => 'placeholder')
        );
        
        // Data Sources
        add_settings_section(
            'smart_chat_data_sources',
            __('منابع داده', 'smart-chat'),
            array($this, 'render_data_sources_section'),
            'smart-chat'
        );
        
        add_settings_field(
            'chat_mode',
            __('حالت چت', 'smart-chat'),
            array($this, 'render_select_field'),
            'smart-chat',
            'smart_chat_data_sources',
            array(
                'field' => 'chat_mode',
                'options' => array(
                    'internal' => __('فقط داخلی', 'smart-chat'),
                    'external' => __('فقط API خارجی', 'smart-chat'),
                    'hybrid' => __('ترکیبی', 'smart-chat')
                )
            )
        );
        
        add_settings_field(
            'internal_results',
            __('تعداد نتایج داخلی', 'smart-chat'),
            array($this, 'render_number_field'),
            'smart-chat',
            'smart_chat_data_sources',
            array('field' => 'internal_results')
        );
        
        add_settings_field(
            'external_weight',
            __('وزن API خارجی (%)', 'smart-chat'),
            array($this, 'render_number_field'),
            'smart-chat',
            'smart_chat_data_sources',
            array('field' => 'external_weight')
        );
        
        // External Provider
        add_settings_section(
            'smart_chat_external',
            __('ارتباط با API خارجی', 'smart-chat'),
            array($this, 'render_external_section'),
            'smart-chat'
        );
        
        add_settings_field(
            'provider_type',
            __('نوع Provider', 'smart-chat'),
            array($this, 'render_select_field'),
            'smart-chat',
            'smart_chat_external',
            array(
                'field' => 'provider_type',
                'options' => array(
                    'mock' => __('Mock (تست)', 'smart-chat'),
                    'openai' => __('OpenAI', 'smart-chat'),
                    'custom' => __('سفارشی', 'smart-chat')
                )
            )
        );
        
        add_settings_field(
            'api_key',
            __('کلید API', 'smart-chat'),
            array($this, 'render_text_field'),
            'smart-chat',
            'smart_chat_external',
            array('field' => 'api_key')
        );
        
        add_settings_field(
            'api_endpoint',
            __('آدرس Endpoint', 'smart-chat'),
            array($this, 'render_text_field'),
            'smart-chat',
            'smart_chat_external',
            array('field' => 'api_endpoint')
        );
        
        // Appearance
        add_settings_section(
            'smart_chat_appearance',
            __('ظاهر', 'smart-chat'),
            array($this, 'render_appearance_section'),
            'smart-chat'
        );
        
        add_settings_field(
            'primary_color',
            __('رنگ اصلی', 'smart-chat'),
            array($this, 'render_color_field'),
            'smart-chat',
            'smart_chat_appearance',
            array('field' => 'primary_color')
        );
        
        // Privacy & Logs
        add_settings_section(
            'smart_chat_privacy',
            __('حریم خصوصی و لاگ‌ها', 'smart-chat'),
            array($this, 'render_privacy_section'),
            'smart-chat'
        );
        
        add_settings_field(
            'log_enabled',
            __('فعال‌سازی لاگ‌ها', 'smart-chat'),
            array($this, 'render_checkbox_field'),
            'smart-chat',
            'smart_chat_privacy',
            array('field' => 'log_enabled')
        );
        
        add_settings_field(
            'log_retention',
            __('مدت نگهداری لاگ (روز)', 'smart-chat'),
            array($this, 'render_number_field'),
            'smart-chat',
            'smart_chat_privacy',
            array('field' => 'log_retention')
        );
        
        add_settings_field(
            'rate_limit',
            __('محدودیت درخواست (در دقیقه)', 'smart-chat'),
            array($this, 'render_number_field'),
            'smart-chat',
            'smart_chat_privacy',
            array('field' => 'rate_limit')
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $options = get_option('smart_chat_options', array());
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('smart_chat_options');
                do_settings_sections('smart-chat');
                submit_button();
                ?>
            </form>
            
            <div class="smart-chat-test-section">
                <h2><?php _e('تست افزونه', 'smart-chat'); ?></h2>
                <p><?php _e('برای تست عملکرد افزونه، یک پیام ارسال کنید:', 'smart-chat'); ?></p>
                <input type="text" id="test-message" placeholder="<?php _e('پیام تست...', 'smart-chat'); ?>" />
                <button type="button" id="send-test" class="button"><?php _e('ارسال', 'smart-chat'); ?></button>
                <div id="test-response"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render field methods
     */
    public function render_checkbox_field($args) {
        $options = get_option('smart_chat_options', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : false;
        ?>
        <input type="checkbox" name="smart_chat_options[<?php echo esc_attr($field); ?>]" value="1" <?php checked($value, 1); ?> />
        <?php
    }
    
    public function render_text_field($args) {
        $options = get_option('smart_chat_options', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        ?>
        <input type="text" name="smart_chat_options[<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php
    }
    
    public function render_textarea_field($args) {
        $options = get_option('smart_chat_options', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        ?>
        <textarea name="smart_chat_options[<?php echo esc_attr($field); ?>]" rows="3" class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <?php
    }
    
    public function render_select_field($args) {
        $options = get_option('smart_chat_options', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        $select_options = $args['options'];
        ?>
        <select name="smart_chat_options[<?php echo esc_attr($field); ?>]">
            <?php foreach ($select_options as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($value, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }
    
    public function render_number_field($args) {
        $options = get_option('smart_chat_options', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        ?>
        <input type="number" name="smart_chat_options[<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text" />
        <?php
    }
    
    public function render_color_field($args) {
        $options = get_option('smart_chat_options', array());
        $field = $args['field'];
        $value = isset($options[$field]) ? $options[$field] : '';
        ?>
        <input type="color" name="smart_chat_options[<?php echo esc_attr($field); ?>]" value="<?php echo esc_attr($value); ?>" />
        <?php
    }
    
    /**
     * Section descriptions
     */
    public function render_general_section() {
        echo '<p>' . __('تنظیمات عمومی افزونه چت هوشمند', 'smart-chat') . '</p>';
    }
    
    public function render_data_sources_section() {
        echo '<p>' . __('تنظیمات منابع داده و نحوه پاسخگویی', 'smart-chat') . '</p>';
    }
    
    public function render_external_section() {
        echo '<p>' . __('تنظیمات ارتباط با سرویس‌های چت‌بات خارجی', 'smart-chat') . '</p>';
    }
    
    public function render_appearance_section() {
        echo '<p>' . __('تنظیمات ظاهری و رنگ‌بندی', 'smart-chat') . '</p>';
    }
    
    public function render_privacy_section() {
        echo '<p>' . __('تنظیمات حریم خصوصی و لاگ‌گیری', 'smart-chat') . '</p>';
    }
    
    /**
     * Sanitize options
     */
    public function sanitize_options($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? true : false;
        $sanitized['position'] = sanitize_text_field($input['position']);
        $sanitized['welcome_message'] = sanitize_textarea_field($input['welcome_message']);
        $sanitized['placeholder'] = sanitize_text_field($input['placeholder']);
        $sanitized['primary_color'] = sanitize_hex_color($input['primary_color']);
        $sanitized['chat_mode'] = sanitize_text_field($input['chat_mode']);
        $sanitized['internal_results'] = absint($input['internal_results']);
        $sanitized['external_weight'] = absint($input['external_weight']);
        $sanitized['log_enabled'] = isset($input['log_enabled']) ? true : false;
        $sanitized['log_retention'] = absint($input['log_retention']);
        $sanitized['rate_limit'] = absint($input['rate_limit']);
        $sanitized['provider_type'] = sanitize_text_field($input['provider_type']);
        $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        $sanitized['api_endpoint'] = esc_url_raw($input['api_endpoint']);
        
        return $sanitized;
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_smart-chat') {
            return;
        }
        
        wp_enqueue_script(
            'smart-chat-admin',
            SMART_CHAT_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            SMART_CHAT_VERSION,
            true
        );
        
        wp_localize_script('smart-chat-admin', 'smartChatAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smart_chat_admin_nonce'),
        ));
    }
}
