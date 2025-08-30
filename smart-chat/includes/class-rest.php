<?php
/**
 * REST API Class
 *
 * @package SmartChat
 */

namespace SmartChat;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class REST {
    
    /**
     * Initialize REST API
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    /**
     * Register REST routes
     */
    public function register_routes() {
        register_rest_route('smart-chat/v1', '/chat', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_chat_request'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'message' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                    'type' => 'string',
                ),
                'session_id' => array(
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field',
                    'type' => 'string',
                ),
            ),
        ));
        
        register_rest_route('smart-chat/v1', '/search', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_search_request'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'query' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'type' => 'string',
                ),
            ),
        ));
    }
    
    /**
     * Check permission for REST requests
     */
    public function check_permission($request) {
        // Check nonce
        $nonce = $request->get_header('X-WP-Nonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return false;
        }
        
        // Check rate limit
        $rate_limiter = new Rate_Limit();
        if (!$rate_limiter->check_limit()) {
            return new \WP_Error(
                'rate_limit_exceeded',
                __('محدودیت درخواست تجاوز شده است', 'smart-chat'),
                array('status' => 429)
            );
        }
        
        return true;
    }
    
    /**
     * Handle chat request
     */
    public function handle_chat_request($request) {
        $message = $request->get_param('message');
        $session_id = $request->get_param('session_id');
        
        if (empty($message)) {
            return new \WP_Error(
                'invalid_message',
                __('پیام نمی‌تواند خالی باشد', 'smart-chat'),
                array('status' => 400)
            );
        }
        
        // Log the request if enabled
        $this->log_request($message, $session_id);
        
        // Get response based on chat mode
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
        
        return rest_ensure_response($response);
    }
    
    /**
     * Handle search request
     */
    public function handle_search_request($request) {
        $query = $request->get_param('query');
        
        if (empty($query)) {
            return new \WP_Error(
                'invalid_query',
                __('کوئری نمی‌تواند خالی باشد', 'smart-chat'),
                array('status' => 400)
            );
        }
        
        $results = $this->search_internal_content($query);
        
        return rest_ensure_response(array(
            'success' => true,
            'data' => $results,
        ));
    }
    
    /**
     * Get internal response from WordPress content
     */
    private function get_internal_response($message) {
        $options = get_option('smart_chat_options', array());
        $max_results = $options['internal_results'] ?? 5;
        
        $results = $this->search_internal_content($message, $max_results);
        
        if (empty($results)) {
            return array(
                'success' => true,
                'message' => __('متأسفانه پاسخی برای سوال شما پیدا نشد. لطفاً سوال خود را به شکل دیگری مطرح کنید.', 'smart-chat'),
                'sources' => array(),
                'type' => 'internal',
            );
        }
        
        $response_message = $this->format_internal_response($results);
        
        return array(
            'success' => true,
            'message' => $response_message,
            'sources' => $results,
            'type' => 'internal',
        );
    }
    
    /**
     * Get external response from API provider
     */
    private function get_external_response($message) {
        $options = get_option('smart_chat_options', array());
        $provider_type = $options['provider_type'] ?? 'mock';
        
        try {
            $provider = $this->get_provider_instance($provider_type);
            $response = $provider->get_response($message);
            
            return array(
                'success' => true,
                'message' => $response['message'],
                'sources' => $response['sources'] ?? array(),
                'type' => 'external',
                'provider' => $provider_type,
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => __('خطا در ارتباط با سرویس خارجی', 'smart-chat'),
                'error' => $e->getMessage(),
                'type' => 'external',
            );
        }
    }
    
    /**
     * Get hybrid response combining internal and external
     */
    private function get_hybrid_response($message) {
        $options = get_option('smart_chat_options', array());
        $external_weight = $options['external_weight'] ?? 50;
        $internal_weight = 100 - $external_weight;
        
        $internal_response = $this->get_internal_response($message);
        $external_response = $this->get_external_response($message);
        
        // Combine responses based on weights
        $combined_message = '';
        
        if ($internal_response['success'] && $external_response['success']) {
            $combined_message = $internal_response['message'] . "\n\n" . $external_response['message'];
        } elseif ($internal_response['success']) {
            $combined_message = $internal_response['message'];
        } elseif ($external_response['success']) {
            $combined_message = $external_response['message'];
        } else {
            $combined_message = __('متأسفانه پاسخی برای سوال شما پیدا نشد.', 'smart-chat');
        }
        
        return array(
            'success' => true,
            'message' => $combined_message,
            'sources' => array_merge(
                $internal_response['sources'] ?? array(),
                $external_response['sources'] ?? array()
            ),
            'type' => 'hybrid',
        );
    }
    
    /**
     * Search internal WordPress content
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
     * Format internal response message
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
    
    /**
     * Log request for debugging
     */
    private function log_request($message, $session_id) {
        $options = get_option('smart_chat_options', array());
        
        if (empty($options['log_enabled'])) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'message' => $message,
            'session_id' => $session_id,
            'ip' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        );
        
        $logs = get_transient('smart_chat_logs') ?: array();
        $logs[] = $log_entry;
        
        // Keep only recent logs
        $retention_days = $options['log_retention'] ?? 30;
        $cutoff_time = strtotime("-{$retention_days} days");
        
        $logs = array_filter($logs, function($log) use ($cutoff_time) {
            return strtotime($log['timestamp']) > $cutoff_time;
        });
        
        set_transient('smart_chat_logs', $logs, DAY_IN_SECONDS);
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
