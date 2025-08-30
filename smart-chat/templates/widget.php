<?php
/**
 * Chat Widget Template
 *
 * @package SmartChat
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$options = get_option('smart_chat_options', array());
$position = $options['position'] ?? 'bottom-right';
$primary_color = $options['primary_color'] ?? '#007cba';
$welcome_message = $options['welcome_message'] ?? 'سلام! اگه سوالی داری من اینجام 🤚';
$placeholder = $options['placeholder'] ?? 'پیام خود را بنویسید...';
$is_rtl = is_rtl();
?>

<div id="smart-chat-widget" class="smart-chat-widget smart-chat-<?php echo esc_attr($position); ?>" data-rtl="<?php echo $is_rtl ? 'true' : 'false'; ?>">
    
    <!-- Chat Toggle Button -->
    <div class="smart-chat-toggle" style="background-color: <?php echo esc_attr($primary_color); ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H6L4 18V4H20V16Z" fill="white"/>
        </svg>
    </div>
    
    <!-- Chat Window -->
    <div class="smart-chat-window" style="display: none;">
        <div class="smart-chat-header" style="background-color: <?php echo esc_attr($primary_color); ?>">
            <h3><?php _e('چت هوشمند', 'smart-chat'); ?></h3>
            <button class="smart-chat-close" aria-label="<?php _e('بستن چت', 'smart-chat'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="white"/>
                </svg>
            </button>
        </div>
        
        <div class="smart-chat-messages">
            <div class="smart-chat-message smart-chat-bot">
                <div class="smart-chat-avatar">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 5C13.66 5 15 6.34 15 8C15 9.66 13.66 11 12 11C10.34 11 9 9.66 9 8C9 6.34 10.34 5 12 5ZM12 19.2C9.5 19.2 7.29 17.92 6 15.98C6.03 13.99 10 12.9 12 12.9C13.99 12.9 17.97 13.99 18 15.98C16.71 17.92 14.5 19.2 12 19.2Z" fill="<?php echo esc_attr($primary_color); ?>"/>
                    </svg>
                </div>
                <div class="smart-chat-bubble">
                    <?php echo esc_html($welcome_message); ?>
                </div>
            </div>
        </div>
        
        <div class="smart-chat-input">
            <form class="smart-chat-form">
                <input 
                    type="text" 
                    class="smart-chat-text-input" 
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    aria-label="<?php _e('پیام خود را بنویسید', 'smart-chat'); ?>"
                />
                <button type="submit" class="smart-chat-send" style="background-color: <?php echo esc_attr($primary_color); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="white"/>
                    </svg>
                </button>
            </form>
        </div>
        
        <div class="smart-chat-typing" style="display: none;">
            <div class="smart-chat-typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="smart-chat-typing-text"><?php _e('در حال تایپ...', 'smart-chat'); ?></span>
        </div>
    </div>
</div>

<script type="text/javascript">
window.smartChatConfig = {
    position: '<?php echo esc_js($position); ?>',
    primaryColor: '<?php echo esc_js($primary_color); ?>',
    welcomeMessage: '<?php echo esc_js($welcome_message); ?>',
    placeholder: '<?php echo esc_js($placeholder); ?>',
    isRTL: <?php echo $is_rtl ? 'true' : 'false'; ?>,
    ajaxUrl: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
    nonce: '<?php echo esc_js(wp_create_nonce('smart_chat_nonce')); ?>',
    restUrl: '<?php echo esc_js(rest_url('smart-chat/v1/')); ?>',
    restNonce: '<?php echo esc_js(wp_create_nonce('wp_rest')); ?>'
};
</script>
