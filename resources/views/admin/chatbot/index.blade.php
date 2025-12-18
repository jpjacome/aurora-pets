@extends('admin.layout')

@section('title', 'WhatsApp Chatbot')

@section('content')
<div>
    <h1>WhatsApp Chatbot</h1>
    
    <!-- Stats Overview -->
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="ph ph-chats-circle dashboard-card-icon"></i>
                <h2>Total Conversations</h2>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-stat-main">
                    <div class="dashboard-stat-value">0</div>
                    <div class="dashboard-stat-label">All Time</div>
                </div>
                <hr class="dashboard-hr">
                <div class="dashboard-stat-secondary">
                    <div class="dashboard-stat-value-small">0</div>
                    <div class="dashboard-stat-label-small">This Week</div>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="ph ph-chat-circle-dots dashboard-card-icon"></i>
                <h2>Active Chats</h2>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-stat-main">
                    <div class="dashboard-stat-value">0</div>
                    <div class="dashboard-stat-label">Currently Open</div>
                </div>
                <hr class="dashboard-hr">
                <div class="dashboard-stat-secondary">
                    <div class="dashboard-stat-value-small">0</div>
                    <div class="dashboard-stat-label-small">Unread</div>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="ph ph-fire dashboard-card-icon"></i>
                <h2>Hot Leads</h2>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-stat-main">
                    <div class="dashboard-stat-value">0</div>
                    <div class="dashboard-stat-label">High Quality</div>
                </div>
                <hr class="dashboard-hr">
                <div class="dashboard-stat-secondary">
                    <div class="dashboard-stat-value-small">0</div>
                    <div class="dashboard-stat-label-small">New Today</div>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="ph ph-robot dashboard-card-icon"></i>
                <h2>Bot Activity</h2>
            </div>
            <div class="dashboard-card-body">
                <div class="dashboard-stat-main">
                    <div class="dashboard-stat-value">0%</div>
                    <div class="dashboard-stat-label">Automation Rate</div>
                </div>
                <hr class="dashboard-hr">
                <div class="dashboard-stat-secondary">
                    <div class="dashboard-stat-value-small">0</div>
                    <div class="dashboard-stat-label-small">AI Responses</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chatbot Interface -->
    <div class="chatbot-container">
        <!-- Left Panel: Conversations List -->
        <div class="conversations-panel">
            <div class="conversations-header">
                <h2>
                    <i class="ph ph-whatsapp-logo"></i>
                    Conversations
                </h2>
            </div>
            
            <div class="conversations-search">
                <input 
                    type="text" 
                    id="searchConversations" 
                    placeholder="Search conversations..."
                >
            </div>
            
            <div class="conversations-list" id="conversationsList">
                <!-- Sample conversation item (will be replaced with real data) -->
                <div class="conversation-item" data-conversation-id="sample">
                    <div class="conversation-header">
                        <span class="conversation-name">Sample Customer</span>
                        <span class="conversation-time">2:30 PM</span>
                    </div>
                    <p class="conversation-preview">
                        Hola, me gustaría saber más sobre sus servicios...
                    </p>
                    <div class="conversation-meta">
                        <span class="mode-indicator mode-bot">
                            <i class="ph-fill ph-robot"></i> Bot
                        </span>
                        <span class="lead-badge lead-warm">
                            <i class="ph-fill ph-flame"></i> Warm
                        </span>
                        <span class="unread-badge">3</span>
                    </div>
                </div>

                <!-- Empty state (shown when no real conversations) -->
                <div class="chat-empty-state" style="display: none;">
                    <i class="ph ph-chats-circle"></i>
                    <h3>No conversations yet</h3>
                    <p>WhatsApp conversations will appear here when customers message you.</p>
                </div>
            </div>
        </div>

        <!-- Right Panel: Chat View -->
        <div class="chat-panel">
            <div class="chat-empty-state">
                <i class="ph ph-chat-centered-dots"></i>
                <h3>Select a conversation</h3>
                <p>Choose a conversation from the list to view messages and respond.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Search functionality
    const searchInput = document.getElementById('searchConversations');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const conversations = document.querySelectorAll('.conversation-item');
            
            conversations.forEach(conv => {
                const name = conv.querySelector('.conversation-name')?.textContent.toLowerCase() || '';
                const preview = conv.querySelector('.conversation-preview')?.textContent.toLowerCase() || '';
                
                if (name.includes(searchTerm) || preview.includes(searchTerm)) {
                    conv.style.display = '';
                } else {
                    conv.style.display = 'none';
                }
            });
        });
    }

    // Conversation selection
    document.addEventListener('click', function(e) {
        const conversationItem = e.target.closest('.conversation-item');
        if (conversationItem) {
            // Remove active class from all
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active to clicked
            conversationItem.classList.add('active');
            
            // Load conversation (placeholder for now)
            loadConversation(conversationItem.dataset.conversationId);
        }
    });

    function loadConversation(conversationId) {
        // Placeholder - will be implemented with actual API calls
        console.log('Loading conversation:', conversationId);
        
        // For demo: show sample chat
        const chatPanel = document.querySelector('.chat-panel');
        if (chatPanel && conversationId === 'sample') {
            chatPanel.innerHTML = `
                <div class="chat-header">
                    <div class="chat-header-left">
                        <h3 class="chat-contact-name">Sample Customer</h3>
                        <p class="chat-contact-phone">+593 99 123 4567</p>
                    </div>
                    
                    <div class="chat-header-right">
                        <div class="mode-toggle">
                            <span class="mode-toggle-label">Bot Mode</span>
                            <label class="toggle-switch">
                                <input type="checkbox" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="chat-info-bar">
                    <div class="lead-score-selector">
                        <label for="leadScore">Lead Quality:</label>
                        <select id="leadScore">
                            <option value="new">New</option>
                            <option value="cold">Cold</option>
                            <option value="warm" selected>Warm</option>
                            <option value="hot">Hot</option>
                        </select>
                    </div>
                </div>

                <div class="chat-messages">
                    <div class="message-date-divider">
                        <span>Today</span>
                    </div>

                    <div class="message incoming">
                        <div class="message-avatar">
                            <i class="ph ph-user"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">
                                <p class="message-text">Hola, me gustaría saber más sobre sus servicios de paisajismo</p>
                            </div>
                            <div class="message-meta">
                                <span class="message-time">2:30 PM</span>
                            </div>
                        </div>
                    </div>

                    <div class="message outgoing">
                        <div class="message-avatar">
                            <i class="ph ph-robot"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">
                                <p class="message-text">¡Hola! Gracias por contactarnos. En Aurora ofrecemos diseño de jardines personalizados, PlantScan para encontrar la planta perfecta para tu mascota, y mantenimiento de espacios verdes. ¿Qué servicio te interesa más?</p>
                            </div>
                            <div class="message-meta">
                                <span class="message-time">2:31 PM</span>
                                <span class="message-status status-read">
                                    <i class="ph ph-checks"></i>
                                </span>
                                <span class="message-ai-indicator">
                                    <i class="ph ph-robot"></i> AI
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="message incoming">
                        <div class="message-avatar">
                            <i class="ph ph-user"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-bubble">
                                <p class="message-text">Me interesa el PlantScan, tengo un perro y quiero plantas seguras</p>
                            </div>
                            <div class="message-meta">
                                <span class="message-time">2:33 PM</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chat-input-area">
                    <div class="chat-input-wrapper">
                        <textarea class="chat-input" placeholder="Type a message..." rows="1" disabled></textarea>
                        <button class="chat-send-btn" disabled>
                            <i class="ph ph-paper-plane-tilt"></i>
                        </button>
                    </div>
                </div>
            `;
            
            // Scroll to bottom
            const messagesArea = chatPanel.querySelector('.chat-messages');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
        }
    }
</script>
@endsection
