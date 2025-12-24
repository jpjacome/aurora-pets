@extends('admin.layout')

@section('title', 'Test AI Bot')

@section('content')
<div>
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('admin.chatbot.index') }}" style="color: var(--color-3); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
            <i class="ph ph-arrow-left"></i>
            Back to Chatbot
        </a>
    </div>

    <div class="page-top test-top-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin-bottom: 0.5rem;">Test AI Bot</h1>
            <p style="color: var(--color-3); font-size: 0.95rem;">Practice conversations and train AI responses without WhatsApp</p>
        </div>
        <div class="right-controls" style="display: flex; gap: 1rem; align-items: center;">
            <select id="aiProvider" class="price-select" style="padding: 0.5rem 1rem; min-width: 250px;">
                <option value="gemini" selected>Gemini Flash-Lite Latest (cheapest / low-cost)</option>
                <option value="groq">Groq (Llama 3.3)</option>
                <option value="deepseek">DeepSeek (Paid)</option>
            </select>
            <button id="resetChat" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="ph ph-arrow-counter-clockwise"></i>
                Reset Chat
            </button>
        </div>
    </div>

    <div class="test-chatbot-container">
        <!-- Test Chat Interface -->
        <div class="test-chat-panel">
            <div class="test-chat-header">
                <div>
                    <h3 style="margin: 0; color: var(--color-3);">
                        <i class="ph ph-robot"></i>
                        Aurora AI Assistant
                    </h3>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: rgba(220, 255, 214, 0.7);">Powered by Groq Llama 3.3 70B</p>
                </div>
                <div class="ai-status">
                    <span class="status-dot online"></span>
                    <span style="color: var(--color-3); font-size: 0.9rem;">AI Ready</span>
                </div>
            </div>

            <div class="test-chat-messages" id="testChatMessages">
                <div class="chat-empty-state">
                    <i class="ph ph-chat-centered-dots"></i>
                    <h3>Start Testing AI Responses</h3>
                    <p>Type a message below to test how Aurora AI responds to customer inquiries</p>
                </div>
            </div>

            <div class="test-chat-input-area">
                <div class="chat-input-wrapper">
                    <textarea 
                        id="testMessageInput" 
                        class="chat-input" 
                        placeholder="Type a test message as a customer..." 
                        rows="1"
                    ></textarea>
                    <button id="testSendButton" class="chat-send-btn">
                        <i class="ph ph-paper-plane-tilt"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- AI Insights Panel -->
        <div class="ai-insights-panel">
            <!-- Aurora Expression Display -->
            <div class="aurora-expression-container">
                <img id="auroraExpression" 
                     src="{{ asset('images/aurora-expressions/2-2.png') }}" 
                     alt="Aurora" 
                     class="aurora-expression-img">
                <div class="aurora-name">Aurora</div>
                <div id="expressionDebug" style="font-size:0.85rem;color:rgba(220,255,214,0.7); margin-top:0.5rem;">Expression: --</div>
            </div>

            <div class="insights-header">
                <h3>
                    <i class="ph ph-brain"></i>
                    AI Insights
                </h3>
            </div>

            <div class="insights-content">
                <div class="insight-section">
                    <h4>
                        <i class="ph ph-target"></i>
                        Detected Intent
                    </h4>
                    <div id="detectedIntent" class="insight-value">
                        <span style="color: rgba(220, 255, 214, 0.5); font-style: italic;">Send a message to analyze</span>
                    </div>
                </div>

                <div class="insight-section">
                    <h4>
                        <i class="ph ph-activity"></i>
                        Usage (today)
                    </h4>
                    <div id="usageInfo" class="insight-value">
                        <div id="usageRequests" style="font-size:0.95rem;color:var(--color-3);">Requests: --</div>
                        <div id="usageTokens" style="font-size:0.95rem;color:var(--color-3); margin-top:0.25rem;">Tokens: --</div>
                        <div id="usageRemaining" style="font-size:0.85rem;color:rgba(220,255,214,0.7); margin-top:0.25rem;">Remaining (requests): --</div>
                    </div>
                </div>

                <div class="insight-section">
                    <h4>
                        <i class="ph ph-flame"></i>
                        Lead Score
                    </h4>
                    <div id="leadScore" class="insight-value">
                        <span style="color: rgba(220, 255, 214, 0.5); font-style: italic;">Send a message to analyze</span>
                    </div>
                </div>

                <div class="insight-section">
                    <h4>
                        <i class="ph ph-gauge"></i>
                        AI Confidence
                    </h4>
                    <div id="aiConfidence" class="insight-value">
                        <span style="color: rgba(220, 255, 214, 0.5); font-style: italic;">Send a message to analyze</span>
                    </div>
                </div>

                <div class="insight-section">
                    <h4>
                        <i class="ph ph-warning-circle"></i>
                        Escalation Needed?
                    </h4>
                    <div id="escalationStatus" class="insight-value">
                        <span style="color: rgba(220, 255, 214, 0.5); font-style: italic;">Send a message to analyze</span>
                    </div>
                </div>

                <div class="insight-section">
                    <h4>
                        <i class="ph ph-clock"></i>
                        Response Time
                    </h4>
                    <div id="responseTime" class="insight-value">
                        <span style="color: rgba(220, 255, 214, 0.5); font-style: italic;">--</span>
                    </div>
                </div>
            </div>

            <div class="insights-footer">
                <p style="font-size: 0.8rem; color: rgba(220, 255, 214, 0.6); margin: 0;">
                    <i class="ph ph-info"></i>
                    Test conversations are not saved to the database
                </p>
                <div style="margin-top:0.75rem; display:flex; gap:0.5rem; align-items:center;">
                    <button id="addAdminCommentBtn" class="btn-secondary" style="padding:0.5rem 0.75rem;">Add Admin Comment</button>
                    <div id="adminCommentAlert" style="display:none; color: #10b981; font-size:0.9rem; align-self:center;">Saved</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Comment Modal -->
    <div id="adminCommentModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:9999;">
        <div style="background:var(--color-2); padding:1.25rem; width:520px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.4);">
            <h3 style="margin-top:0; color:var(--color-3);">Add Admin Comment</h3>
            <p style="color:rgba(220,255,214,0.7); font-size:0.9rem;">Add a comment and include the conversation context to help later training or knowledge updates.</p>
            <textarea id="adminCommentText" placeholder="Write your comment..." style="width:100%; min-height:120px; padding:0.75rem; box-sizing:border-box; background:rgba(220,255,214,0.05); color:var(--color-3); border:1px solid rgba(220,255,214,0.1);"></textarea>
            <input type="hidden" id="adminConversationContext" name="conversation_context">
            <div style="display:flex; justify-content:flex-end; gap:0.5rem; margin-top:0.75rem;">
                <button id="adminCommentCancel" class="btn-secondary">Cancel</button>
                <button id="adminCommentSave" class="btn-primary">Save Comment</button>
            </div>
            <div id="adminCommentErrors" style="color:#ef4444; margin-top:0.5rem; display:none;"></div>
        </div>
    </div>

    <!-- View Admin Comment Modal -->
    <div id="adminCommentViewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:9999;">
        <div style="background:var(--color-2); padding:1.25rem; width:640px; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.4); max-height:80vh; overflow:auto;">
            <h3 id="viewCommentTitle" style="margin-top:0; color:var(--color-3);">Admin Comment</h3>
            <p id="viewCommentMeta" style="color:rgba(220,255,214,0.7); font-size:0.9rem;">&nbsp;</p>
            <div id="viewCommentBody" style="background:rgba(220,255,214,0.03); padding:0.75rem; border-radius:6px; color:var(--color-3);">&nbsp;</div>
            <h4 style="margin-top:0.75rem; color:var(--color-3);">Conversation Context</h4>
            <pre id="viewCommentContext" style="background:rgba(0,0,0,0.2); padding:0.75rem; border-radius:6px; color:#fff; white-space:pre-wrap;">&nbsp;</pre>
            <div style="display:flex; justify-content:flex-end; gap:0.5rem; margin-top:0.75rem;">
                <button id="adminCommentViewClose" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
@endsection

@section('scripts')
<script>
    let conversationHistory = [];
    let messageCount = 0;

    // Auto-resize textarea
    const messageInput = document.getElementById('testMessageInput');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Send message
    const sendButton = document.getElementById('testSendButton');
    if (sendButton && messageInput) {
        sendButton.addEventListener('click', sendTestMessage);
        
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendTestMessage();
            }
        });
    }

    function sendTestMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        // Clear empty state if first message
        const chatMessages = document.getElementById('testChatMessages');
        if (messageCount === 0) {
            chatMessages.innerHTML = '';
        }

        // Add user message
        addMessage('user', message);
        conversationHistory.push({
            role: 'user',
            content: message
        });

        // Clear input and reset height
        messageInput.value = '';
        messageInput.style.height = 'auto';

        // Disable input while processing
        messageInput.disabled = true;
        sendButton.disabled = true;

        // Show typing indicator
        showTypingIndicator();

        // Record start time for response time calculation
        const startTime = Date.now();

        // Send to backend for AI response
        const provider = document.getElementById('aiProvider').value;
        
        fetch('/admin/chatbot/test/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                message: message,
                conversation_history: conversationHistory,
                provider: provider
            })
        })
        .then(response => response.json())
        .then(data => {
            // Calculate response time
            const responseTime = Date.now() - startTime;
            document.getElementById('responseTime').innerHTML = 
                `<span style="color: var(--color-1); font-weight: 600;">${(responseTime / 1000).toFixed(2)}s</span>`;

            // Remove typing indicator
            removeTypingIndicator();

            if (data.success) {
                // Add AI response
                addMessage('ai', data.response);

                // Determine expression to attach to this assistant message
                let expressionForMessage = null;
                if (data.insights && data.insights.expression) {
                    expressionForMessage = data.insights.expression;
                } else {
                    // Fallback: infer from the last user message and AI response
                    try {
                        const lastUser = conversationHistory.slice().reverse().find(m => m.role === 'user');
                        expressionForMessage = detectExpressionClient(lastUser ? lastUser.content : '', data.response);
                    } catch (e) {
                        expressionForMessage = null;
                    }
                }

                conversationHistory.push({
                    role: 'assistant',
                    content: data.response,
                    expression: expressionForMessage
                });

                // Update insights
                updateInsights(data.insights);
            } else {
                addMessage('error', 'Error: ' + (data.error || 'Failed to get AI response'));
            }

            // Re-enable input
            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
        })
        .catch(error => {
            console.error('Error:', error);
            removeTypingIndicator();
            addMessage('error', 'Connection error. Please try again.');
            messageInput.disabled = false;
            sendButton.disabled = false;
        });
    }

    function addMessage(type, content) {
        const chatMessages = document.getElementById('testChatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type === 'user' ? 'outgoing' : 'incoming'}`;
        
        if (type === 'error') {
            messageDiv.innerHTML = `
                <div class="message-content" style="width: 100%; text-align: center;">
                    <div class="message-bubble" style="background: rgba(220, 38, 38, 0.1); border: 1px solid rgba(220, 38, 38, 0.3);">
                        <p class="message-text" style="color: #dc2626;">${content}</p>
                    </div>
                </div>
            `;
        } else {
            const time = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="ph ${type === 'user' ? 'ph-user' : 'ph-robot'}"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble">
                        <p class="message-text">${escapeHtml(content)}</p>
                    </div>
                    <div class="message-meta">
                        <span class="message-time">${time}</span>
                        ${type === 'ai' ? '<span class="message-ai-indicator"><i class="ph ph-robot"></i> Test AI</span>' : ''}
                    </div>
                </div>
            `;
        }

        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        messageCount++;
    }

    function showTypingIndicator() {
        const chatMessages = document.getElementById('testChatMessages');
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message incoming typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="message-avatar">
                <i class="ph ph-robot"></i>
            </div>
            <div class="message-content">
                <div class="message-bubble">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function removeTypingIndicator() {
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    function removeDiacritics(str) {
        if (!str) return '';
        try {
            return str.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();
        } catch (e) {
            // Fallback for older environments
            return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }
    }

    function detectExpressionClient(userMessage, aiResponse) {
        const user = removeDiacritics(userMessage || '');
        const ai = removeDiacritics(aiResponse || '');

        // Emergency
        const emergency = ['urgente','acaba de','fallecio','murio','accidente','necesito ahora','ayuda urgente','grave','problema grave','me preocupa','es grave','preocupante'];
        if (emergency.some(k => user.includes(k))) return '2-3';

        // Deep grief
        const grief = ['perdi','se fue','ya no esta','partio','lo extraño','la extraño','hace poco','ayer','anoche','fallecio','murio'];
        if (grief.some(k => user.includes(k))) {
            if (user.includes('ayer') || user.includes('anoche') || user.includes('hoy')) return '1-3';
            return '3-3';
        }

        // Happy / new pet
        const happy = ['nueva','nuevo','cachorro','gatito','bebe','adopte','feliz','alegre','adoptado'];
        if (happy.some(k => user.includes(k))) return '1-2';

        // Questions
        if (user.includes('?') || user.includes('como') || user.includes('que')) return '1-1';

        // B2B
        const b2b = ['veterinaria','veterinario','clinica','distribucion','mayorista','negocio','empresa'];
        if (b2b.some(k => user.includes(k))) return '2-3';

        // Confirm
        const confirm = ['si','esta bien','ok','entiendo','gracias','perfecto','claro'];
        if (confirm.some(k => user.includes(k))) return '3-1';

        // Concern in AI response
        const concern = ['importante','recuerda','ten en cuenta','cuidado'];
        if (concern.some(k => ai.includes(k))) return '2-1';

        return '2-2';
    }

    function updateInsights(insights) {
        // Update detected intent
        const intentMap = {
            'product_inquiry': { text: 'Product Inquiry', color: 'var(--color-1)' },
            'pricing': { text: 'Pricing Question', color: '#f59e0b' },
            'service_question': { text: 'Service Question', color: 'var(--color-1)' },
            'appointment': { text: 'Appointment Request', color: '#10b981' },
            'complaint': { text: 'Complaint/Issue', color: '#ef4444' },
            'general': { text: 'General Inquiry', color: 'var(--color-3)' }
        };
        const intent = intentMap[insights.intent] || { text: insights.intent, color: 'var(--color-3)' };
        document.getElementById('detectedIntent').innerHTML = 
            `<span style="color: ${intent.color}; font-weight: 600;">${intent.text}</span>`;

        // Update lead score
        const scoreMap = {
            'hot': { text: '🔥 Hot', color: '#ef4444' },
            'warm': { text: '⚡ Warm', color: '#f59e0b' },
            'cold': { text: '❄️ Cold', color: '#3b82f6' },
            'new': { text: '✨ New', color: 'var(--color-3)' }
        };
        const score = scoreMap[insights.lead_score] || { text: insights.lead_score, color: 'var(--color-3)' };
        document.getElementById('leadScore').innerHTML = 
            `<span style="color: ${score.color}; font-weight: 600;">${score.text}</span>`;

        // Update confidence
        const confidence = Math.round(insights.confidence * 100);
        const confidenceColor = confidence >= 80 ? '#10b981' : confidence >= 60 ? '#f59e0b' : '#ef4444';
        document.getElementById('aiConfidence').innerHTML = 
            `<span style="color: ${confidenceColor}; font-weight: 600;">${confidence}%</span>`;

        // Update usage info if provided
        if (insights.usage) {
            document.getElementById('usageRequests').textContent = `Requests: ${insights.usage.requests}${insights.usage.request_limit ? ' / ' + insights.usage.request_limit : ''}`;
            document.getElementById('usageTokens').textContent = `Tokens: ${insights.usage.tokens}`;
            document.getElementById('usageRemaining').textContent = `Remaining (requests): ${insights.usage.remaining_requests !== null ? insights.usage.remaining_requests : '—'}`;
        } else {
            document.getElementById('usageRequests').textContent = 'Requests: --';
            document.getElementById('usageTokens').textContent = 'Tokens: --';
            document.getElementById('usageRemaining').textContent = 'Remaining (requests): --';
        }

        // Update Aurora expression (server-provided or client fallback)
        const expressionImg = document.getElementById('auroraExpression');
        if (insights.expression) {
            expressionImg.src = `/images/aurora-expressions/${insights.expression}.png`;
            const dbg = document.getElementById('expressionDebug');
            if (dbg) dbg.textContent = `Expression: ${insights.expression} (server)`;
        } else {
            // Fallback: infer from last user message and AI response
            try {
                const lastUser = conversationHistory.slice().reverse().find(m => m.role === 'user');
                const lastAI = conversationHistory.slice().reverse().find(m => m.role === 'assistant');
                let inferred = detectExpressionClient(lastUser ? lastUser.content : '', lastAI ? lastAI.content : '');
                // If this is a follow-up (we have history) and inference gave welcome, prefer attentive
                if (conversationHistory.length > 0 && inferred === '2-2') {
                    inferred = '1-1';
                }
                expressionImg.src = `/images/aurora-expressions/${inferred}.png`;
                const dbg = document.getElementById('expressionDebug');
                if (dbg) dbg.textContent = `Expression: ${inferred} (client fallback)`;
            } catch (e) {
                console.warn('Expression fallback failed', e);
            }
        }
        // Add subtle animation
        expressionImg.style.opacity = '0.7';
        setTimeout(() => {
            expressionImg.style.opacity = '1';
        }, 150);

        // Update escalation status
        const escalation = insights.should_escalate;
        const escalationColor = escalation ? '#ef4444' : '#10b981';
        const escalationIcon = escalation ? 'ph-warning' : 'ph-check-circle';
        document.getElementById('escalationStatus').innerHTML = 
            `<span style="color: ${escalationColor}; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                <i class="ph ${escalationIcon}"></i>
                ${escalation ? 'Yes - Manual Required' : 'No - AI Can Handle'}
            </span>`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Reset chat
    document.getElementById('resetChat').addEventListener('click', function() {
        if (confirm('Reset the conversation? This will clear all messages.')) {
            conversationHistory = [];
            messageCount = 0;
            document.getElementById('testChatMessages').innerHTML = `
                <div class="chat-empty-state">
                    <i class="ph ph-chat-centered-dots"></i>
                    <h3>Start Testing AI Responses</h3>
                    <p>Type a message below to test how Aurora AI responds to customer inquiries</p>
                </div>
            `;
            // Reset insights
            const insightValue = '<span style="color: rgba(220, 255, 214, 0.5); font-style: italic;">Send a message to analyze</span>';
            document.getElementById('detectedIntent').innerHTML = insightValue;
            document.getElementById('leadScore').innerHTML = insightValue;
            document.getElementById('aiConfidence').innerHTML = insightValue;
            document.getElementById('escalationStatus').innerHTML = insightValue;
            document.getElementById('responseTime').innerHTML = 
                '<span style="color: rgba(220, 255, 214, 0.5); font-style: italic;">--</span>';
        }
    });

    // Admin comment modal logic
    const addAdminCommentBtn = document.getElementById('addAdminCommentBtn');
    const adminModal = document.getElementById('adminCommentModal');
    const adminCancel = document.getElementById('adminCommentCancel');
    const adminSave = document.getElementById('adminCommentSave');
    const adminText = document.getElementById('adminCommentText');
    const adminContextInput = document.getElementById('adminConversationContext');
    const adminErrors = document.getElementById('adminCommentErrors');
    const adminAlert = document.getElementById('adminCommentAlert');

    function openAdminModal() {
        adminText.value = '';
        adminErrors.style.display = 'none';
        adminErrors.textContent = '';
        // serialize current conversation history
        try {
            adminContextInput.value = JSON.stringify(conversationHistory);
        } catch (e) {
            adminContextInput.value = '';
        }
        adminModal.style.display = 'flex';
    }

    function closeAdminModal() {
        adminModal.style.display = 'none';
    }

    if (addAdminCommentBtn) addAdminCommentBtn.addEventListener('click', openAdminModal);
    if (adminCancel) adminCancel.addEventListener('click', function (e) { e.preventDefault(); closeAdminModal(); });

    if (adminSave) {
        adminSave.addEventListener('click', async function (e) {
            e.preventDefault();
            adminErrors.style.display = 'none';
            adminErrors.textContent = '';
            adminSave.disabled = true;
            adminSave.textContent = 'Saving...';

            const payload = {
                comment: adminText.value.trim(),
                conversation_context: adminContextInput.value || null
            };

            try {
                const res = await fetch('{{ route('admin.chatbot.comment.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                // Handle common error status codes before parsing body
                if (res.status === 419) {
                    adminErrors.style.display = 'block';
                    adminErrors.textContent = 'Session expired. Please reload the page and try again.';
                    adminSave.disabled = false;
                    adminSave.textContent = 'Save Comment';
                    return;
                }
                if (res.status === 403) {
                    adminErrors.style.display = 'block';
                    adminErrors.textContent = 'You do not have permission to perform this action.';
                    adminSave.disabled = false;
                    adminSave.textContent = 'Save Comment';
                    return;
                }

                let data = null;
                try {
                    data = await res.json();
                } catch (e) {
                    adminErrors.style.display = 'block';
                    adminErrors.textContent = `Server error (${res.status}). Check server logs.`;
                    adminSave.disabled = false;
                    adminSave.textContent = 'Save Comment';
                    console.error('Error parsing JSON response', e);
                    return;
                }

                if (!res.ok || !data.success) {
                    let msg = (data && data.errors) ? Object.values(data.errors).flat().join(' ') : (data && data.message) ? data.message : `Failed to save comment (status: ${res.status})`;
                    if (data && data.error) msg += ' - ' + data.error;
                    adminErrors.style.display = 'block';
                    adminErrors.textContent = msg;
                    adminSave.disabled = false;
                    adminSave.textContent = 'Save Comment';
                    return;
                }

                // Success
                adminAlert.style.display = 'block';
                setTimeout(() => adminAlert.style.display = 'none', 3000);
                closeAdminModal();
            } catch (err) {
                adminErrors.style.display = 'block';
                adminErrors.textContent = 'Connection error. Please try again.';
            } finally {
                adminSave.disabled = false;
                adminSave.textContent = 'Save Comment';
            }
        });
    }

    const adminViewModal = document.getElementById('adminCommentViewModal');
    const adminViewClose = document.getElementById('adminCommentViewClose');

    if (adminViewClose) adminViewClose.addEventListener('click', () => adminViewModal.style.display = 'none');
</script>
@endsection
