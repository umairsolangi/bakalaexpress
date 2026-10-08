@extends($isSeller ? 'seller.layouts.app' : 'layouts.app')
    
    @section('styles')
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    /* Message animations */
    .message {
        transition: all 0.3s ease;
        opacity: 1;
        transform: translateY(0);
    }
    .message.new-message {
        animation: fadeInUp 0.4s ease forwards;
    }
    .message.sending {
        opacity: 0.7;
    }
    .message.sent {
        opacity: 1;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Smooth scrolling */
    .chat-messages {
        scroll-behavior: smooth;
    }
</style>
    @endsection
    
    @section('content')
<div class="container">
        <div class="row justify-content-center">
        <div class="col-lg-{{ $isSeller ? '10' : '8' }}">
                <div class="chat-card">
                    <div class="chat-header">
                        <div>
                            <div class="chat-title">Chat for Order #{{ $order->id }}</div>
                            <div class="chat-subtitle">
                                <strong>Chat with:</strong> 
                            {{ $isSeller ? $order->user->name : $order->seller->name }}
                            </div>
                            <div class="chat-subtitle">
                                <strong>Payment Mode:</strong> 
                                {{ $order->transaction_id ? 'Online (Transaction ID: ' . $order->transaction_id . ')' : 'Cash on Delivery' }}
                            </div>
                        </div>
                        <a href="{{ $backUrl }}" class="chat-back-btn">
                            <i class="fas fa-arrow-left"></i> Back to Order
                        </a>
                    </div>
                    <div class="chat-messages" id="chat-messages">
                        @foreach($messages as $message)
                        <div class="message {{ ($isSeller && $message->sender_type === 'seller') || (!$isSeller && $message->sender_type === 'user') ? 'sent' : 'received' }}" data-id="{{ $message->id }}">
                                <div class="message-bubble">
                                    {{ $message->message }}
                                    <div class="message-info">
                                        {{ $message->created_at->format('M d, H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="chat-input-container">
                        <form id="message-form" class="chat-input-form">
                        @csrf
                        <input type="text" id="message-input" name="message" class="chat-input" placeholder="Type your message..." autocomplete="off" required>
                            <button class="chat-send-btn" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
    
    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chat-messages');
            const messageForm = document.getElementById('message-form');
            const messageInput = document.getElementById('message-input');
        
        // Track highest message ID to avoid duplicates
        let lastMessageId = getHighestMessageId();
        
        // Check for new messages every 5 seconds (reduced from 3s to lower server load)
        let pollingInterval = setInterval(fetchNewMessages, 5000);
        
        // Handle tab visibility change
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                // User has switched back to this tab - fetch messages immediately
                fetchNewMessages();
            }
        });
        
        // Handle form submission with AJAX to prevent page reload
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
                const message = messageInput.value.trim();
            if (!message) return;
            
            // Create a temporary message that looks like it's sent
            const tempId = 'temp-' + Date.now();
                    const tempMessage = {
                id: tempId,
                sender_type: '{{ $isSeller ? "seller" : "user" }}',
                        message: message,
                        created_at: new Date().toISOString()
                    };
                    
            // Add the message to the chat immediately with sending state
            appendMessageElement(tempMessage, 'sending');
            
            // Clear input
                    messageInput.value = '';
                    
                    // Send to server
            const formData = new FormData();
            formData.append('message', message);
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            
            fetch('{{ $isSeller ? route("seller.chat.send", $order->id) : route("chat.send", $order->id) }}', {
                        method: 'POST',
                        headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
                    .then(data => {
                if (data.success) {
                    // Remove temporary message and add the real one with correct ID
                    const tempElement = document.querySelector(`.message[data-id="${tempId}"]`);
                    if (tempElement) {
                        // Smooth transition - just update the ID and class instead of removing
                        tempElement.setAttribute('data-id', data.message.id);
                        tempElement.classList.remove('sending');
                        tempElement.classList.add('sent');
                        
                        // Update the time
                        const timeElement = tempElement.querySelector('.message-info');
                        if (timeElement) {
                            const date = new Date(data.message.created_at);
                            timeElement.textContent = date.toLocaleString('default', { 
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
                        }
                    } else {
                        // Fallback: add the real message if temp isn't found
                        appendMessageElement(data.message);
                    }
                    
                    lastMessageId = Math.max(lastMessageId, data.message.id);
                } else {
                    // Show the error in the UI
                    const tempElement = document.querySelector(`.message[data-id="${tempId}"]`);
                    if (tempElement) {
                        const messageBubble = tempElement.querySelector('.message-bubble');
                        if (messageBubble) {
                            messageBubble.innerHTML += '<div class="message-error">Failed to send</div>';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                // Visual feedback for error
                const tempElement = document.querySelector(`.message[data-id="${tempId}"]`);
                if (tempElement) {
                    const messageBubble = tempElement.querySelector('.message-bubble');
                    if (messageBubble) {
                        messageBubble.innerHTML += '<div class="message-error">Network error</div>';
                    }
                }
            });
        });
        
        // Get highest message ID from current chat
        function getHighestMessageId() {
            const messages = document.querySelectorAll('.message[data-id]');
            let maxId = 0;
            messages.forEach(message => {
                const id = parseInt(message.getAttribute('data-id'), 10);
                if (!isNaN(id) && id > maxId) {
                    maxId = id;
                }
            });
            return maxId;
        }
        
        // Fetch only new messages since lastMessageId to avoid re-fetching all messages
        function fetchNewMessages() {
            const url = '{{ $isSeller ? route("seller.chat.get-messages", $order->id) : route("chat.get-messages", $order->id) }}';
            fetch(url + '?since=' + lastMessageId, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let hasNewMessages = false;
                    
                    // Add only messages that are new
                    data.messages.forEach(message => {
                        if (message.id > lastMessageId) {
                            appendMessageElement(message, 'new-message');
                            lastMessageId = Math.max(lastMessageId, message.id);
                            hasNewMessages = true;
                        }
                    });
                    
                    // If new messages, scroll to bottom
                    if (hasNewMessages) {
                        scrollToBottom();
                        
                        // Mark messages as read
                        markMessagesAsRead();
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
            });
        }
        
        // Add a new message to the chat
        function appendMessageElement(message, extraClass = '') {
            // Check if this message already exists (except for temp messages)
            if (!message.id.toString().includes('temp-') && document.querySelector(`.message[data-id="${message.id}"]`)) {
                return;
            }
                
                const messageDiv = document.createElement('div');
            messageDiv.className = `message ${({{ $isSeller ? 'true' : 'false' }} && message.sender_type === 'seller') || (!{{ $isSeller ? 'true' : 'false' }} && message.sender_type === 'user') ? 'sent' : 'received'} ${extraClass}`;
            messageDiv.setAttribute('data-id', message.id);
    
                const messageBubble = document.createElement('div');
                messageBubble.className = 'message-bubble';
                messageBubble.textContent = message.message;
    
                const messageInfo = document.createElement('div');
                messageInfo.className = 'message-info';
                
                const date = new Date(message.created_at);
                messageInfo.textContent = date.toLocaleString('default', { 
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
    
                messageBubble.appendChild(messageInfo);
                messageDiv.appendChild(messageBubble);
                chatContainer.appendChild(messageDiv);
                
            // Scroll to the new message
            scrollToBottom();
        }
        
        // Mark messages as read
        function markMessagesAsRead() {
            fetch('{{ $isSeller ? route("seller.chat.mark-read", $order->id) : route("chat.mark-read", $order->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });
        }
        
        // Scroll to bottom of chat
        function scrollToBottom() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        // Initial scroll to bottom
        scrollToBottom();
            
        // Focus on input field
            messageInput.focus();
        
        // Mark messages as read initially
        markMessagesAsRead();
        
        // Initial fetch to make sure we have the latest messages
        fetchNewMessages();
        });
    </script>
    @endsection