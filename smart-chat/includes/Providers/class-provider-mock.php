<?php
/**
 * Mock Provider for testing
 *
 * @package SmartChat
 */

namespace SmartChat\Providers;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Provider_Mock implements Provider_Interface {
    
    /**
     * Get mock response
     */
    public function get_response($message) {
        // Simple keyword-based responses for testing
        $responses = array(
            'سلام' => 'سلام! چطور می‌تونم کمکتون کنم؟',
            'خوبی' => 'ممنون، من خوبم. امیدوارم شما هم خوب باشید!',
            'سوال' => 'بله، حتماً. لطفاً سوال خود را مطرح کنید.',
            'کمک' => 'من اینجا هستم تا کمکتان کنم. چه کمکی از دستم برمی‌آید؟',
            'تشکر' => 'خواهش می‌کنم! خوشحالم که توانستم کمکتان کنم.',
            'خداحافظ' => 'خداحافظ! امیدوارم باز هم ببینمتان.',
        );
        
        $message_lower = mb_strtolower(trim($message), 'UTF-8');
        
        foreach ($responses as $keyword => $response) {
            if (mb_strpos($message_lower, $keyword, 0, 'UTF-8') !== false) {
                return array(
                    'message' => $response,
                    'sources' => array(),
                    'confidence' => 0.8,
                );
            }
        }
        
        // Default response
        $default_responses = array(
            'متأسفانه متوجه سوال شما نشدم. لطفاً سوال خود را به شکل دیگری مطرح کنید.',
            'این سوال خارج از حوزه تخصص من است. لطفاً سوال دیگری بپرسید.',
            'متأسفانه اطلاعات کافی برای پاسخ به این سوال ندارم.',
            'لطفاً سوال خود را واضح‌تر مطرح کنید تا بتوانم کمکتان کنم.',
        );
        
        $random_response = $default_responses[array_rand($default_responses)];
        
        return array(
            'message' => $random_response,
            'sources' => array(),
            'confidence' => 0.3,
        );
    }
    
    /**
     * Check if provider is available
     */
    public function is_available() {
        return true; // Mock provider is always available
    }
    
    /**
     * Get provider name
     */
    public function get_name() {
        return __('Mock Provider (تست)', 'smart-chat');
    }
    
    /**
     * Get provider description
     */
    public function get_description() {
        return __('این provider برای تست و توسعه افزونه استفاده می‌شود. پاسخ‌های ساده و از پیش تعریف شده ارائه می‌دهد.', 'smart-chat');
    }
}
