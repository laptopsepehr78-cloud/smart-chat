<?php
/**
 * Provider Interface
 *
 * @package SmartChat
 */

namespace SmartChat\Providers;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

interface Provider_Interface {
    
    /**
     * Get response from provider
     *
     * @param string $message User message
     * @return array Response data
     */
    public function get_response($message);
    
    /**
     * Check if provider is available
     *
     * @return bool
     */
    public function is_available();
    
    /**
     * Get provider name
     *
     * @return string
     */
    public function get_name();
    
    /**
     * Get provider description
     *
     * @return string
     */
    public function get_description();
}
