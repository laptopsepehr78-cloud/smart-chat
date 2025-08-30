<?php
/**
 * Basic Tests for Smart Chat Plugin
 *
 * @package SmartChat
 */

class SmartChatBasicTest extends WP_UnitTestCase {
    
    public function test_plugin_loaded() {
        $this->assertTrue(class_exists('SmartChat\Loader'));
    }
    
    public function test_admin_class_exists() {
        $this->assertTrue(class_exists('SmartChat\Admin'));
    }
    
    public function test_rest_class_exists() {
        $this->assertTrue(class_exists('SmartChat\REST'));
    }
    
    public function test_router_class_exists() {
        $this->assertTrue(class_exists('SmartChat\Router'));
    }
    
    public function test_rate_limit_class_exists() {
        $this->assertTrue(class_exists('SmartChat\Rate_Limit'));
    }
    
    public function test_provider_interface_exists() {
        $this->assertTrue(interface_exists('SmartChat\Providers\Provider_Interface'));
    }
    
    public function test_mock_provider_exists() {
        $this->assertTrue(class_exists('SmartChat\Providers\Provider_Mock'));
    }
    
    public function test_default_options_exist() {
        $options = get_option('smart_chat_options');
        $this->assertIsArray($options);
        $this->assertArrayHasKey('enabled', $options);
        $this->assertArrayHasKey('position', $options);
        $this->assertArrayHasKey('welcome_message', $options);
    }
}
