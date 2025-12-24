@extends('admin.layout')

@section('title', 'Conversation - ' . ($conversation->client?->client ?? $conversation->contact_name ?? $conversation->phone_number))

@section('content')
<div>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('admin.chatbot.index') }}" style="color: var(--color-3); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-arrow-left"></i>
            Back to Conversations
        </a>
    </div>

    <div class="chatbot-container" style="height: calc(100vh - 200px);">
        <!-- Left Panel: Conversation Info -->
        <div class="conversations-panel" style="max-width: 300px;">
            <div class="conversations-header">
                <h2>
                    <i class="ph ph-info"></i>
                    Info
                </h2>
            </div>
            
            <div style="padding: 1.5rem;">
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: var(--color-3); font-size: 1rem; margin-bottom: 0.5rem;">Contact</h3>
                    <p style="color: var(--color-3); font-size: 1.1rem; font-weight: 600; margin-bottom: 0.25rem;">
                        @if($conversation->client)
                            {{ $conversation->client->client }}
                        @elseif($conversation->contact_name)
                            {{ $conversation->contact_name }}
                        @else
                            Unknown Contact
                        @endif
                    </p>
                    <p style="color: rgba(220, 255, 214, 0.7); font-size: 0.9rem;">
                        {{ $conversation->phone_number }}
                    </p>
                </div>

                @if($conversation->client)
                    <div style="margin-bottom: 1.5rem; padding: 1rem; background: rgba(220, 255, 214, 0.05); border-radius: 8px; border: 1px solid rgba(220, 255, 214, 0.1);">
                        <h3 style="color: var(--color-3); font-size: 0.9rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="ph ph-paw-print"></i>
                            Client Info
                        </h3>
                        <div style="color: rgba(220, 255, 214, 0.8); font-size: 0.85rem; line-height: 1.6;">
                            @if($conversation->client->pet_name)
                                <p><strong>Pet:</strong> {{ $conversation->client->pet_name }}</p>
                            @endif
                            @if($conversation->client->pet_species)
                                <p><strong>Species:</strong> {{ $conversation->client->pet_species }}</p>
                            @endif
                            @if($conversation->client->plant)
                                <p><strong>Plant:</strong> {{ $conversation->client->plant }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: var(--color-3); font-size: 0.9rem; margin-bottom: 0.75rem;">Lead Quality</h3>
                    <select 
                        id="leadScoreSelect" 
                        class="lead-score-selector"
                        style="width: 100%; padding: 0.75rem; background: rgba(220, 255, 214, 0.05); color: var(--color-3); border: 1px solid var(--color-1); border-radius: 8px; box-sizing: border-box; font-size: 0.95rem;"
                        data-conversation-id="{{ $conversation->id }}"
                    >
                        <option value="new" {{ $conversation->lead_score === 'new' ? 'selected' : '' }}>New</option>
                        <option value="cold" {{ $conversation->lead_score === 'cold' ? 'selected' : '' }}>Cold</option>
                        <option value="warm" {{ $conversation->lead_score === 'warm' ? 'selected' : '' }}>Warm</option>
                        <option value="hot" {{ $conversation->lead_score === 'hot' ? 'selected' : '' }}>Hot</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h3 style="color: var(--color-3); font-size: 0.9rem; margin-bottom: 0.75rem;">Bot Mode</h3>
                    <div class="mode-toggle">
                        <label class="toggle-switch">
                            <input 
                                type="checkbox" 
                                id="botModeToggle"
                                {{ $conversation->is_bot_mode ? 'checked' : '' }}
                                data-conversation-id="{{ $conversation->id }}"
                            >
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="mode-toggle-label" style="color: var(--color-3);">
                            {{ $conversation->is_bot_mode ? 'AI Enabled' : 'Manual Mode' }}
                        </span>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(220, 255, 214, 0.1);">
                    <button 
                        onclick="archiveConversation({{ $conversation->id }})"
                        style="width: 100%; padding: 0.75rem; background: rgba(220, 255, 214, 0.05); color: var(--color-3); border: 1px solid rgba(220, 255, 214, 0.2); border-radius: 8px; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;"
                    >
                        <i class="ph ph-archive"></i>
                        Archive Conversation
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Panel: Chat Messages -->
        <div class="chat-panel" style="flex: 1;">
            <div class="chat-header">
                <div class="chat-header-left">
                    <h3 class="chat-contact-name">
                        @if($conversation->client)
                            {{ $conversation->client->client }}
                        @elseif($conversation->contact_name)
                            {{ $conversation->contact_name }}
                        @else
                            {{ $conversation->phone_number }}
                        @endif
                    </h3>
                    <p class="chat-contact-phone">{{ $conversation->phone_number }}</p>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                @forelse($conversation->messages->groupBy(function($msg) { return $msg->created_at->format('Y-m-d'); }) as $date => $messages)
                    <div class="message-date-divider">
                        <span>{{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</span>
                    </div>

                    @foreach($messages as $message)
                        <div class="message {{ $message->direction }}">
                            <div class="message-avatar">
                                <i class="ph {{ $message->direction === 'incoming' ? 'ph-user' : ($message->sent_by_bot ? 'ph-robot' : 'ph-user-circle') }}"></i>
                            </div>
                            <div class="message-content">
                                <div class="message-bubble">
                                    <p class="message-text">{{ $message->content }}</p>
                                </div>
                                <div class="message-meta">
                                    <span class="message-time">{{ $message->created_at->format('g:i A') }}</span>
                                    @if($message->direction === 'outgoing')
                                        <span class="message-status status-{{ $message->status }}">
                                            @if($message->status === 'read')
                                                <i class="ph ph-checks"></i>
                                            @elseif($message->status === 'delivered')
                                                <i class="ph ph-check"></i>
                                            @elseif($message->status === 'sent')
                                                <i class="ph ph-check"></i>
                                            @else
                                                <i class="ph ph-clock"></i>
                                            @endif
                                        </span>
                                        @if($message->sent_by_bot)
                                            <span class="message-ai-indicator">
                                                <i class="ph ph-robot"></i> AI
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="chat-empty-state">
                        <i class="ph ph-chat-centered-dots"></i>
                        <h3>No messages yet</h3>
                        <p>Start the conversation by sending a message below.</p>
                    </div>
                @endforelse
            </div>

            <div class="chat-input-area">
                <div class="chat-input-wrapper">
                    <textarea 
                        id="messageInput" 
                        class="chat-input" 
                        placeholder="Type a message..." 
                        rows="1"
                    ></textarea>
                    <button id="sendButton" class="chat-send-btn">
                        <i class="ph ph-paper-plane-tilt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-scroll to bottom of chat on load
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Send message functionality
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');

    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendMessage);
        
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        // Disable input while sending
        messageInput.disabled = true;
        sendButton.disabled = true;

        fetch(`/admin/chatbot/conversations/{{ $conversation->id }}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload page to show new message
                window.location.reload();
            } else {
                alert('Failed to send message. Please try again.');
                messageInput.disabled = false;
                sendButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send message. Please try again.');
            messageInput.disabled = false;
            sendButton.disabled = false;
        });
    }

    // Bot mode toggle
    const botModeToggle = document.getElementById('botModeToggle');
    if (botModeToggle) {
        botModeToggle.addEventListener('change', function() {
            const conversationId = this.dataset.conversationId;
            const isBotMode = this.checked;

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
                if (data.success) {
                    // Update label
                    const label = this.closest('.mode-toggle').querySelector('.mode-toggle-label');
                    label.textContent = data.is_bot_mode ? 'AI Enabled' : 'Manual Mode';
                } else {
                    // Revert toggle
                    this.checked = !isBotMode;
                    alert('Failed to update mode. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isBotMode;
                alert('Failed to update mode. Please try again.');
            });
        });
    }

    // Lead score update
    const leadScoreSelect = document.getElementById('leadScoreSelect');
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
                if (!data.success) {
                    alert('Failed to update lead score. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update lead score. Please try again.');
            });
        });
    }

    // Archive conversation
    function archiveConversation(conversationId) {
        if (!confirm('Are you sure you want to archive this conversation?')) {
            return;
        }

        fetch(`/admin/chatbot/conversations/${conversationId}/archive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/admin/chatbot';
            } else {
                alert('Failed to archive conversation. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to archive conversation. Please try again.');
        });
    }
</script>
@endsection
