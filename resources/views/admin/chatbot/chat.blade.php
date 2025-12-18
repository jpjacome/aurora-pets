{{-- Chat panel component - to be included when a conversation is selected --}}
<div class="chat-header">
    <div class="chat-header-left">
        <h3 class="chat-contact-name">{{ $conversation->contact_name ?? 'Unknown' }}</h3>
        <p class="chat-contact-phone">{{ $conversation->phone_number ?? '' }}</p>
    </div>
    
    <div class="chat-header-right">
        <!-- Bot/Manual Toggle -->
        <div class="mode-toggle">
            <span class="mode-toggle-label" id="modeLabel">
                {{ $conversation->is_bot_mode ? 'Bot Mode' : 'Manual Mode' }}
            </span>
            <label class="toggle-switch">
                <input 
                    type="checkbox" 
                    id="botModeToggle"
                    {{ $conversation->is_bot_mode ? 'checked' : '' }}
                    data-conversation-id="{{ $conversation->id }}"
                >
                <span class="toggle-slider"></span>
            </label>
        </div>
        
        <!-- More Actions -->
        <button class="chat-action-btn" title="View contact details">
            <i class="ph ph-user"></i>
        </button>
        <button class="chat-action-btn" title="Conversation settings">
            <i class="ph ph-gear"></i>
        </button>
    </div>
</div>

<!-- Chat Info Bar -->
<div class="chat-info-bar">
    <div class="lead-score-selector">
        <label for="leadScore">Lead Quality:</label>
        <select id="leadScore" data-conversation-id="{{ $conversation->id }}">
            <option value="new" {{ $conversation->lead_score === 'new' ? 'selected' : '' }}>New</option>
            <option value="cold" {{ $conversation->lead_score === 'cold' ? 'selected' : '' }}>Cold</option>
            <option value="warm" {{ $conversation->lead_score === 'warm' ? 'selected' : '' }}>Warm</option>
            <option value="hot" {{ $conversation->lead_score === 'hot' ? 'selected' : '' }}>Hot</option>
        </select>
    </div>
    
    <div class="chat-actions">
        <button class="chat-action-btn" onclick="exportChat({{ $conversation->id }})">
            <i class="ph ph-download-simple"></i> Export
        </button>
        <button class="chat-action-btn" onclick="archiveChat({{ $conversation->id }})">
            <i class="ph ph-archive"></i> Archive
        </button>
    </div>
</div>

<!-- Messages Area -->
<div class="chat-messages" id="chatMessages">
    @forelse($messages as $date => $messageGroup)
        <!-- Date divider -->
        <div class="message-date-divider">
            <span>{{ $date }}</span>
        </div>
        
        @foreach($messageGroup as $message)
            <div class="message {{ $message->direction === 'incoming' ? 'incoming' : 'outgoing' }}">
                <div class="message-avatar">
                    @if($message->direction === 'incoming')
                        <i class="ph ph-user"></i>
                    @else
                        @if($message->sent_by_bot)
                            <i class="ph ph-robot"></i>
                        @else
                            <i class="ph ph-headset"></i>
                        @endif
                    @endif
                </div>
                
                <div class="message-content">
                    <div class="message-bubble">
                        <p class="message-text">{{ $message->content }}</p>
                    </div>
                    
                    <div class="message-meta">
                        <span class="message-time">
                            {{ $message->created_at->format('g:i A') }}
                        </span>
                        
                        @if($message->direction === 'outgoing')
                            <span class="message-status status-{{ $message->status }}">
                                @if($message->status === 'sent')
                                    <i class="ph ph-check"></i>
                                @elseif($message->status === 'delivered')
                                    <i class="ph ph-checks"></i>
                                @elseif($message->status === 'read')
                                    <i class="ph ph-checks" style="color: #25D366;"></i>
                                @endif
                            </span>
                        @endif
                        
                        @if($message->sent_by_bot)
                            <span class="message-ai-indicator">
                                <i class="ph ph-robot"></i> AI
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @empty
        <div class="chat-empty-state">
            <i class="ph ph-chat-centered-text"></i>
            <h3>No messages yet</h3>
            <p>Start the conversation by sending a message.</p>
        </div>
    @endforelse
</div>

<!-- Chat Input Area -->
<div class="chat-input-area">
    <form id="sendMessageForm" class="chat-input-wrapper">
        @csrf
        <textarea 
            id="messageInput"
            class="chat-input" 
            placeholder="Type a message..."
            rows="1"
            {{ $conversation->is_bot_mode ? 'disabled' : '' }}
        ></textarea>
        <button 
            type="submit" 
            class="chat-send-btn"
            {{ $conversation->is_bot_mode ? 'disabled' : '' }}
        >
            <i class="ph ph-paper-plane-tilt"></i>
        </button>
    </form>
</div>

<script>
    // Auto-resize textarea
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Send on Enter (Shift+Enter for new line)
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('sendMessageForm').dispatchEvent(new Event('submit'));
            }
        });
    }

    // Bot mode toggle
    const botToggle = document.getElementById('botModeToggle');
    const modeLabel = document.getElementById('modeLabel');
    
    if (botToggle) {
        botToggle.addEventListener('change', function() {
            const conversationId = this.dataset.conversationId;
            const isBotMode = this.checked;
            
            // Update UI
            modeLabel.textContent = isBotMode ? 'Bot Mode' : 'Manual Mode';
            messageInput.disabled = isBotMode;
            document.querySelector('.chat-send-btn').disabled = isBotMode;
            
            // Send update to server
            fetch(`/admin/chatbot/conversations/${conversationId}/toggle-mode`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_bot_mode: isBotMode })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Mode updated:', data);
            })
            .catch(error => {
                console.error('Error updating mode:', error);
                // Revert on error
                botToggle.checked = !isBotMode;
                modeLabel.textContent = !isBotMode ? 'Bot Mode' : 'Manual Mode';
            });
        });
    }

    // Lead score change
    const leadScoreSelect = document.getElementById('leadScore');
    if (leadScoreSelect) {
        leadScoreSelect.addEventListener('change', function() {
            const conversationId = this.dataset.conversationId;
            const leadScore = this.value;
            
            fetch(`/admin/chatbot/conversations/${conversationId}/update-lead-score`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ lead_score: leadScore })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Lead score updated:', data);
            })
            .catch(error => {
                console.error('Error updating lead score:', error);
            });
        });
    }

    // Send message
    const sendMessageForm = document.getElementById('sendMessageForm');
    if (sendMessageForm) {
        sendMessageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message) return;
            
            const conversationId = {{ $conversation->id }};
            
            // Clear input immediately
            messageInput.value = '';
            messageInput.style.height = 'auto';
            
            // Send to server
            fetch(`/admin/chatbot/conversations/${conversationId}/send`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Message sent:', data);
                // Reload messages (in real implementation, use WebSocket or polling)
                location.reload();
            })
            .catch(error => {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
                messageInput.value = message; // Restore message on error
            });
        });
    }

    // Auto-scroll to bottom
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function exportChat(conversationId) {
        window.location.href = `/admin/chatbot/conversations/${conversationId}/export`;
    }

    function archiveChat(conversationId) {
        if (confirm('Are you sure you want to archive this conversation?')) {
            fetch(`/admin/chatbot/conversations/${conversationId}/archive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                alert('Conversation archived successfully');
                window.location.href = '/admin/chatbot';
            })
            .catch(error => {
                console.error('Error archiving:', error);
                alert('Failed to archive conversation');
            });
        }
    }
</script>
