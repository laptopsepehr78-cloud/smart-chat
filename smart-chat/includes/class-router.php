<?php
/**
 * Router Class for handling AJAX requests
 *
 * @package SmartChat
 */

namespace SmartChat;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Router {
    
    /**
     * Initialize router
     */
    public function __construct() {
        add_action('wp_ajax_smart_chat_message', array($this, 'handle_ajax_message'));
        add_action('wp_ajax_nopriv_smart_chat_message', array($this, 'handle_ajax_message'));
        add_action('wp_ajax_smart_chat_search', array($this, 'handle_ajax_search'));
        add_action('wp_ajax_nopriv_smart_chat_search', array($this, 'handle_ajax_search'));
    }
    
    /**
     * Handle AJAX message request
     */
    public function handle_ajax_message() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'smart_chat_nonce')) {
            wp_die(__('خطای امنیتی', 'smart-chat'));
        }
        
        // Check rate limit
        $rate_limiter = new Rate_Limit();
        if (!$rate_limiter->check_limit()) {
            wp_send_json_error(__('محدودیت درخواست تجاوز شده است', 'smart-chat'));
        }
        
        $message = sanitize_textarea_field($_POST['message']);
        $session_id = sanitize_text_field($_POST['session_id'] ?? '');
        
        if (empty($message)) {
            wp_send_json_error(__('پیام نمی‌تواند خالی باشد', 'smart-chat'));
        }
        
        // Get response
        $options = get_option('smart_chat_options', array());
        $chat_mode = $options['chat_mode'] ?? 'internal';
        
        $response = array();
        
        switch ($chat_mode) {
            case 'internal':
                $response = $this->get_internal_response($message);
                break;
                
            case 'external':
                $response = $this->get_external_response($message);
                break;
                
            case 'hybrid':
                $response = $this->get_hybrid_response($message);
                break;
                
            default:
                $response = $this->get_internal_response($message);
        }
        
        wp_send_json_success($response);
    }
    
    /**
     * Handle AJAX search request
     */
    public function handle_ajax_search() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'smart_chat_nonce')) {
            wp_die(__('خطای امنیتی', 'smart-chat'));
        }
        
        $query = sanitize_text_field($_POST['query']);
        
        if (empty($query)) {
            wp_send_json_error(__('کوئری نمی‌تواند خالی باشد', 'smart-chat'));
        }
        
        $results = $this->search_internal_content($query);
        wp_send_json_success($results);
    }
    
    /**
     * Get internal response
     */
    private function get_internal_response($message) {
        $options = get_option('smart_chat_options', array());
        $max_results = $options['internal_results'] ?? 5;
        
        $results = $this->search_internal_content($message, $max_results);
        
        if (empty($results)) {
            return array(
                'message' => __('متأسفانه پاسخی برای سوال شما پیدا نشد. لطفاً سوال خود را به شکل دیگری مطرح کنید.', 'smart-chat'),
                'sources' => array(),
                'type' => 'internal',
            );
        }
        
        $response_message = $this->format_internal_response($results);
        
        return array(
            'message' => $response_message,
            'sources' => $results,
            'type' => 'internal',
        );
    }
    
    /**
     * Get external response
     */
    private function get_external_response($message) {
        $options = get_option('smart_chat_options', array());
        $provider_type = $options['provider_type'] ?? 'mock';
        
        try {
            $provider = $this->get_provider_instance($provider_type);
            $response = $provider->get_response($message);
            
            return array(
                'message' => $response['message'],
                'sources' => $response['sources'] ?? array(),
                'type' => 'external',
                'provider' => $provider_type,
            );
        } catch (\Exception $e) {
            return array(
                'message' => __('خطا در ارتباط با سرویس خارجی', 'smart-chat'),
                'error' => $e->getMessage(),
                'type' => 'external',
            );
        }
    }
    
    /**
     * Get hybrid response
     */
    private function get_hybrid_response($message) {
        $options = get_option('smart_chat_options', array());
        $external_weight = $options['external_weight'] ?? 50;
        
        $internal_response = $this->get_internal_response($message);
        $external_response = $this->get_external_response($message);
        
        $combined_message = '';
        
        if ($internal_response['message'] && $external_response['message']) {
            $combined_message = $internal_response['message'] . "\n\n" . $external_response['message'];
        } elseif ($internal_response['message']) {
            $combined_message = $internal_response['message'];
        } elseif ($external_response['message']) {
            $combined_message = $external_response['message'];
        } else {
            $combined_message = __('متأسفانه پاسخی برای سوال شما پیدا نشد.', 'smart-chat');
        }
        
        return array(
            'message' => $combined_message,
            'sources' => array_merge(
                $internal_response['sources'] ?? array(),
                $external_response['sources'] ?? array()
            ),
            'type' => 'hybrid',
        );
    }
    
    /**
     * Search internal content
     */
    private function search_internal_content($query, $max_results = 5) {
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'posts_per_page' => $max_results,
            's' => $query,
            'orderby' => 'relevance',
        );
        
        $search_query = new \WP_Query($args);
        $results = array();
        
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'url' => get_permalink(),
                    'type' => get_post_type(),
                );
            }
        }
        
        wp_reset_postdata();
        
        return $results;
    }
    
    /**
     * Format internal response
     */
    private function format_internal_response($results) {
        if (empty($results)) {
            return __('متأسفانه پاسخی برای سوال شما پیدا نشد.', 'smart-chat');
        }
        
        $message = __('بر اساس جستجو در محتوای سایت، موارد زیر را پیدا کردم:', 'smart-chat') . "\n\n";
        
        foreach ($results as $result) {
            $message .= "• " . $result['title'] . "\n";
            $message .= "  " . $result['excerpt'] . "\n";
            $message .= "  " . __('مشاهده کامل:', 'smart-chat') . " " . $result['url'] . "\n\n";
        }
        
        return trim($message);
    }
    
    /**
     * Get provider instance
     */
    private function get_provider_instance($provider_type) {
        switch ($provider_type) {
            case 'mock':
                return new Providers\Provider_Mock();
            case 'openai':
                return new Providers\Provider_OpenAI();
            case 'custom':
                return new Providers\Provider_Custom();
            default:
                return new Providers\Provider_Mock();
        }
    }
}
