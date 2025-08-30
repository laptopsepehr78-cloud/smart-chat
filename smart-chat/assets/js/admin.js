/**
 * Smart Chat Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        const $testMessage = $('#test-message');
        const $sendTest = $('#send-test');
        const $testResponse = $('#test-response');
        
        // Test message functionality
        $sendTest.on('click', function() {
            const message = $testMessage.val().trim();
            
            if (!message) {
                alert('لطفاً پیام تست وارد کنید');
                return;
            }
            
            $sendTest.prop('disabled', true).text('در حال ارسال...');
            $testResponse.html('<p>در حال ارسال پیام...</p>');
            
            // Send test message via AJAX
            $.ajax({
                url: smartChatAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'smart_chat_test_message',
                    message: message,
                    nonce: smartChatAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $testResponse.html(`
                            <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 4px; margin-top: 10px;">
                                <h4 style="margin: 0 0 10px 0; color: #155724;">پاسخ دریافت شد:</h4>
                                <p style="margin: 0; color: #155724;">${response.data.message}</p>
                                ${response.data.sources && response.data.sources.length > 0 ? 
                                    `<div style="margin-top: 10px;">
                                        <strong>منابع:</strong><br>
                                        ${response.data.sources.map(source => 
                                            `<a href="${source.url}" target="_blank">${source.title}</a>`
                                        ).join('<br>')}
                                    </div>` : ''
                                }
                            </div>
                        `);
                    } else {
                        $testResponse.html(`
                            <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 4px; margin-top: 10px;">
                                <h4 style="margin: 0 0 10px 0; color: #721c24;">خطا:</h4>
                                <p style="margin: 0; color: #721c24;">${response.data || 'خطای نامشخص'}</p>
                            </div>
                        `);
                    }
                },
                error: function() {
                    $testResponse.html(`
                        <div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 4px; margin-top: 10px;">
                            <h4 style="margin: 0 0 10px 0; color: #721c24;">خطا:</h4>
                            <p style="margin: 0; color: #721c24;">خطا در ارتباط با سرور</p>
                        </div>
                    `);
                },
                complete: function() {
                    $sendTest.prop('disabled', false).text('ارسال');
                }
            });
        });
        
        // Enter key support for test message
        $testMessage.on('keypress', function(e) {
            if (e.which === 13) {
                $sendTest.click();
            }
        });
        
        // Color picker enhancement
        $('input[type="color"]').on('change', function() {
            const color = $(this).val();
            $(this).css('background-color', color);
        });
        
        // Dynamic field visibility based on chat mode
        $('select[name="smart_chat_options[chat_mode]"]').on('change', function() {
            const mode = $(this).val();
            const $externalFields = $('.smart-chat-external-field');
            const $hybridFields = $('.smart-chat-hybrid-field');
            
            if (mode === 'external') {
                $externalFields.show();
                $hybridFields.hide();
            } else if (mode === 'hybrid') {
                $externalFields.show();
                $hybridFields.show();
            } else {
                $externalFields.hide();
                $hybridFields.hide();
            }
        });
        
        // Trigger change event on page load
        $('select[name="smart_chat_options[chat_mode]"]').trigger('change');
        
        // Settings validation
        $('form').on('submit', function(e) {
            const chatMode = $('select[name="smart_chat_options[chat_mode]"]').val();
            const apiKey = $('input[name="smart_chat_options[api_key]"]').val();
            const apiEndpoint = $('input[name="smart_chat_options[api_endpoint]"]').val();
            
            if ((chatMode === 'external' || chatMode === 'hybrid') && !apiKey) {
                e.preventDefault();
                alert('برای استفاده از حالت خارجی یا ترکیبی، لطفاً کلید API را وارد کنید');
                return false;
            }
            
            if (chatMode === 'custom' && !apiEndpoint) {
                e.preventDefault();
                alert('برای استفاده از Provider سفارشی، لطفاً آدرس Endpoint را وارد کنید');
                return false;
            }
        });
        
        // Auto-save settings
        let saveTimeout;
        $('input, select, textarea').on('change', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() {
                $('.smart-chat-auto-save').text('تنظیمات ذخیره شدند').fadeIn().delay(2000).fadeOut();
            }, 1000);
        });
    });
    
})(jQuery);
