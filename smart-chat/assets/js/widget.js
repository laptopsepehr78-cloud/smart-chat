/**
 * Smart Chat Widget JavaScript
 */

(function() {
    'use strict';
    
    class SmartChatWidget {
        constructor() {
            this.isOpen = false;
            this.isTyping = false;
            this.sessionId = this.generateSessionId();
            this.init();
        }
        
        init() {
            this.createElements();
            this.bindEvents();
            this.loadConfig();
        }
        
        createElements() {
            // Widget container already exists in DOM
            this.widget = document.getElementById('smart-chat-widget');
            this.toggle = this.widget.querySelector('.smart-chat-toggle');
            this.window = this.widget.querySelector('.smart-chat-window');
            this.messages = this.widget.querySelector('.smart-chat-messages');
            this.form = this.widget.querySelector('.smart-chat-form');
            this.input = this.widget.querySelector('.smart-chat-text-input');
            this.sendButton = this.widget.querySelector('.smart-chat-send');
            this.closeButton = this.widget.querySelector('.smart-chat-close');
            this.typingIndicator = this.widget.querySelector('.smart-chat-typing');
        }
        
        bindEvents() {
            this.toggle.addEventListener('click', () => this.toggleChat());
            this.closeButton.addEventListener('click', () => this.closeChat());
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
            this.input.addEventListener('keypress', (e) => this.handleKeypress(e));
            
            // Close on outside click
            document.addEventListener('click', (e) => this.handleOutsideClick(e));
            
            // Trap focus in chat window
            this.window.addEventListener('keydown', (e) => this.trapFocus(e));
        }
        
        loadConfig() {
            // Config is loaded from PHP template
            this.config = window.smartChatConfig || {};
            
            // Set RTL if needed
            if (this.config.isRTL) {
                this.widget.setAttribute('data-rtl', 'true');
            }
        }
        
        toggleChat() {
            if (this.isOpen) {
                this.closeChat();
            } else {
                this.openChat();
            }
        }
        
        openChat() {
            this.isOpen = true;
            this.window.style.display = 'flex';
            this.toggle.style.display = 'none';
            this.input.focus();
            
            // Add open class for animations
            this.widget.classList.add('smart-chat-open');
        }
        
        closeChat() {
            this.isOpen = false;
            this.window.style.display = 'none';
            this.toggle.style.display = 'flex';
            
            // Remove open class
            this.widget.classList.remove('smart-chat-open');
        }
        
        handleSubmit(e) {
            e.preventDefault();
            const message = this.input.value.trim();
            
            if (!message) {
                return;
            }
            
            this.sendMessage(message);
            this.input.value = '';
        }
        
        handleKeypress(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.form.dispatchEvent(new Event('submit'));
            }
        }
        
        handleOutsideClick(e) {
            if (!this.isOpen) return;
            
            if (!this.widget.contains(e.target)) {
                this.closeChat();
            }
        }
        
        trapFocus(e) {
            if (e.key === 'Tab') {
                const focusableElements = this.window.querySelectorAll(
                    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                );
                
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];
                
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        lastElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        firstElement.focus();
                        e.preventDefault();
                    }
                }
            }
        }
        
        sendMessage(message) {
            // Add user message
            this.addMessage(message, 'user');
            
            // Show typing indicator
            this.showTyping();
            
            // Send to server
            this.sendToServer(message);
        }
        
        addMessage(message, type, sources = []) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `smart-chat-message smart-chat-${type}`;
            
            let avatar = '';
            if (type === 'bot') {
                avatar = `
                    <div class="smart-chat-avatar">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 5C13.66 5 15 6.34 15 8C15 9.66 13.66 11 12 11C10.34 11 9 9.66 9 8C9 6.34 10.34 5 12 5ZM12 19.2C9.5 19.2 7.29 17.92 6 15.98C6.03 13.99 10 12.9 12 12.9C13.99 12.9 17.97 13.99 18 15.98C16.71 17.92 14.5 19.2 12 19.2Z" fill="${this.config.primaryColor || '#007cba'}"/>
                        </svg>
                    </div>
                `;
            }
            
            let sourcesHtml = '';
            if (sources && sources.length > 0) {
                sourcesHtml = `
                    <div class="smart-chat-sources">
                        <strong>منابع:</strong><br>
                        ${sources.map(source => 
                            `<a href="${source.url}" target="_blank">${source.title}</a>`
                        ).join('<br>')}
                    </div>
                `;
            }
            
            messageDiv.innerHTML = `
                ${avatar}
                <div class="smart-chat-bubble">
                    ${this.escapeHtml(message)}
                    ${sourcesHtml}
                </div>
            `;
            
            this.messages.appendChild(messageDiv);
            this.scrollToBottom();
        }
        
        showTyping() {
            this.isTyping = true;
            this.typingIndicator.style.display = 'flex';
            this.scrollToBottom();
        }
        
        hideTyping() {
            this.isTyping = false;
            this.typingIndicator.style.display = 'none';
        }
        
        async sendToServer(message) {
            try {
                // Try REST API first
                if (this.config.restUrl) {
                    const response = await this.sendViaREST(message);
                    this.handleResponse(response);
                    return;
                }
            } catch (error) {
                console.warn('REST API failed, falling back to AJAX:', error);
            }
            
            // Fallback to AJAX
            this.sendViaAJAX(message);
        }
        
        async sendViaREST(message) {
            const response = await fetch(this.config.restUrl + 'chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': this.config.restNonce
                },
                body: JSON.stringify({
                    message: message,
                    session_id: this.sessionId
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            return await response.json();
        }
        
        sendViaAJAX(message) {
            const formData = new FormData();
            formData.append('action', 'smart_chat_message');
            formData.append('message', message);
            formData.append('session_id', this.sessionId);
            formData.append('nonce', this.config.nonce);
            
            fetch(this.config.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => this.handleResponse(data))
            .catch(error => {
                console.error('AJAX error:', error);
                this.handleError();
            });
        }
        
        handleResponse(data) {
            this.hideTyping();
            
            if (data.success && data.data) {
                const response = data.data;
                this.addMessage(response.message, 'bot', response.sources);
            } else {
                this.handleError();
            }
        }
        
        handleError() {
            this.hideTyping();
            this.addMessage('متأسفانه خطایی رخ داده است. لطفاً دوباره تلاش کنید.', 'bot');
        }
        
        scrollToBottom() {
            this.messages.scrollTop = this.messages.scrollHeight;
        }
        
        generateSessionId() {
            return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }
    
    // Initialize widget when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new SmartChatWidget();
        });
    } else {
        new SmartChatWidget();
    }
    
})();
