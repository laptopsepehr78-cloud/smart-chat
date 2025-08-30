<?php
/**
 * Rate Limiting Class
 *
 * @package SmartChat
 */

namespace SmartChat;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Rate_Limit {
    
    /**
     * Check if request is within rate limit
     */
    public function check_limit() {
        $options = get_option('smart_chat_options', array());
        $rate_limit = $options['rate_limit'] ?? 10; // requests per minute
        
        $client_ip = $this->get_client_ip();
        $transient_key = 'smart_chat_rate_limit_' . md5($client_ip);
        
        $requests = get_transient($transient_key);
        
        if ($requests === false) {
            // First request, set initial count
            set_transient($transient_key, 1, MINUTE_IN_SECONDS);
            return true;
        }
        
        if ($requests >= $rate_limit) {
            return false; // Rate limit exceeded
        }
        
        // Increment request count
        set_transient($transient_key, $requests + 1, MINUTE_IN_SECONDS);
        return true;
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
    
    /**
     * Get current request count for client
     */
    public function get_current_count() {
        $client_ip = $this->get_client_ip();
        $transient_key = 'smart_chat_rate_limit_' . md5($client_ip);
        
        return get_transient($transient_key) ?: 0;
    }
    
    /**
     * Get remaining requests for client
     */
    public function get_remaining_requests() {
        $options = get_option('smart_chat_options', array());
        $rate_limit = $options['rate_limit'] ?? 10;
        
        $current_count = $this->get_current_count();
        return max(0, $rate_limit - $current_count);
    }
    
    /**
     * Reset rate limit for client (admin function)
     */
    public function reset_limit($client_ip = null) {
        if ($client_ip === null) {
            $client_ip = $this->get_client_ip();
        }
        
        $transient_key = 'smart_chat_rate_limit_' . md5($client_ip);
        delete_transient($transient_key);
        
        return true;
    }
}
