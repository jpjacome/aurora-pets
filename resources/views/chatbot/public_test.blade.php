@extends('layouts.public')

@section('title', 'Test Aurora AI')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chatbot-public.css') }}">
@endpush

@section('content')
@include('partials.header')
<div class='background'>
    <video id="chatbotBackgroundVideo" playsinline autoplay muted loop preload="auto" poster="{{ asset('assets/imgs/bg1.png') }}">
        <source src="{{ asset('assets/vids/3.mp4') }}" type="video/mp4">
    </video>
</div>
<div class="public-chat-page">
    <main class="public-chat-container">
        <section class="public-chat-panel" aria-label="Chat">

            <div class="public-chat-messages" id="publicChatMessages">
                <div class="public-chat-empty"></div>
                <h3 class="hello-message">Hola, soy Aurora 🧡 ¿En qué puedo ayudarte hoy?</h3>
            </div>

            <form class="public-chat-input-form" id="publicChatForm" onsubmit="return false;">
                <select id="publicAiProvider" class="public-ai-provider" aria-label="AI provider">
                    <option value="gemini" selected>Gemini (low-cost)</option>
                    <option value="groq">Groq (Llama 3.3)</option>
                    <option value="deepseek">DeepSeek</option>
                </select>
                <textarea id="publicMessageInput" class="public-chat-input" placeholder="" rows="1" aria-label="Message"></textarea>
                <button id="publicSendButton" type="button" class="public-send-btn" aria-label="Send message">Enviar</button>
            </form>

            <div class="public-insights" id="publicInsights">
                <div class="insight-row"><strong>Intento detectado:</strong> <span id="detectedIntent">—</span></div>
                <div class="insight-row"><strong>Puntuación:</strong> <span id="leadScore">—</span></div>
                <div class="insight-row"><strong>Confianza:</strong> <span id="aiConfidence">—</span></div>
                <div class="insight-row"><strong>Escalar?</strong> <span id="escalationStatus">—</span></div>
                <div class="insight-row"><strong>Tiempo:</strong> <span id="responseTime">—</span></div>
            </div>
        </section>

        <aside class="public-side-panel" aria-label="Aurora">
            <div class="aurorabot-container">
                <img id="publicAuroraImg" class="public-aurora-img" src="{{ asset('images/aurora-expressions/2-2.png') }}" alt="Aurora expression">

            </div>
        </aside>
    </main>
</div>

    <footer>
        <div class="container fade-in">
            <img id="footer-logo-image" src="{{ asset('assets/logo-hor.png') }}" alt="">
            <img id="footer-logo-imageb" src="{{ asset('assets/logo4.png') }}" alt="">
            <div class="info">
                <p>+593 9 9784 402</p>
                <p>info@aurorapets.com</p>
            </div>
        </div>
    </footer>
@endsection

@push('scripts')
<script>
// Minimal client-side behavior for public testing (no backend calls)
(function(){
    const msgInput = document.getElementById('publicMessageInput');
    const sendBtn = document.getElementById('publicSendButton');
    const messages = document.getElementById('publicChatMessages');
    let conversationHistory = [];

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function addMessage(role, text) {
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        const el = document.createElement('div');
        el.className = `public-chat-message ${role === 'user' ? 'public-outgoing' : 'public-incoming'}`;
        el.innerHTML = `
            <div class="public-message-bubble">
                <p class="public-message-text">${escapeHtml(text)}</p>
                <div class="public-message-meta">${time}</div>
            </div>
        `;
        messages.appendChild(el);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeDiacritics(str) {
        if (!str) return '';
        try {
            return str.normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();
        } catch (e) {
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

    function simulateReply(userText) {
        // Simple canned reply for public testing
        const reply = "Thanks for testing Aurora! This is an automated public-test reply.";
        setTimeout(() => {
            addMessage('ai', reply);
            // Update expression as a subtle change
            const img = document.getElementById('publicAuroraImg');
            if (img) {
                img.src = '/images/aurora-expressions/1-2.png';
                setTimeout(() => img.src = '/images/aurora-expressions/2-2.png', 2500);
            }
            // record simulated assistant message in conversation history
            conversationHistory.push({ role: 'assistant', content: reply });
        }, 700);
    }

    function fadeHelloMessage(){
        const hello = document.querySelector('.hello-message');
        if (!hello) return;
        // Prevent duplicate attempts
        if (hello.dataset.fading === '1') return;
        hello.dataset.fading = '1';
        hello.classList.add('fade-out');
        // remove after transition
        setTimeout(()=>{ try { hello.remove(); } catch(e){} }, 450);
    }

    function updateInsights(insights){
        // Update detected intent
        const intentMap = {
            'product_inquiry': { text: 'Product Inquiry', color: 'var(--color-1)' },
            'pricing': { text: 'Pricing Question', color: '#f59e0b' },
            'service_question': { text: 'Service Question', color: 'var(--color-1)' },
            'appointment': { text: 'Appointment Request', color: '#10b981' },
            'complaint': { text: 'Complaint/Issue', color: '#ef4444' },
            'general': { text: 'General Inquiry', color: 'var(--color-3)' }
        };
        const intent = (insights && intentMap[insights.intent]) ? intentMap[insights.intent] : { text: (insights && insights.intent) || '—', color: 'var(--color-3)' };
        document.getElementById('detectedIntent').innerHTML = `<span style="color: ${intent.color}; font-weight: 600;">${intent.text}</span>`;

        // lead score
        const scoreMap = {
            'hot': { text: '🔥 Hot', color: '#ef4444' },
            'warm': { text: '⚡ Warm', color: '#f59e0b' },
            'cold': { text: '❄️ Cold', color: '#3b82f6' },
            'new': { text: '✨ New', color: 'var(--color-3)' }
        };
        const score = (insights && scoreMap[insights.lead_score]) ? scoreMap[insights.lead_score] : { text: (insights && insights.lead_score) || '—', color: 'var(--color-3)' };
        document.getElementById('leadScore').innerHTML = `<span style="color: ${score.color}; font-weight: 600;">${score.text}</span>`;

        // confidence
        if (insights && typeof insights.confidence !== 'undefined'){
            const confidence = Math.round(insights.confidence * 100);
            const confidenceColor = confidence >= 80 ? '#10b981' : confidence >= 60 ? '#f59e0b' : '#ef4444';
            document.getElementById('aiConfidence').innerHTML = `<span style="color: ${confidenceColor}; font-weight: 600;">${confidence}%</span>`;
        }

        // usage (if provided)
        if (insights && insights.usage){
            // Not displayed in public UI currently, but kept for parity
        }

        // expression: prefer server-provided, otherwise fallback to client inference
        const expressionImg = document.getElementById('publicAuroraImg');
        try {
            if (insights && insights.expression){
                console.log('updateInsights: using server expression', insights.expression);
                if (expressionImg) expressionImg.src = `/images/aurora-expressions/${insights.expression}.png?t=${Date.now()}`;
            } else {
                const lastUser = conversationHistory.slice().reverse().find(m => m.role === 'user');
                const lastAI = conversationHistory.slice().reverse().find(m => m.role === 'assistant');
                let inferred = detectExpressionClient(lastUser ? lastUser.content : '', lastAI ? lastAI.content : '');
                // If this is a follow-up (we have history) and inference gave welcome, prefer attentive
                if (conversationHistory.length > 0 && inferred === '2-2') inferred = '1-1';
                console.log('updateInsights: using inferred expression', inferred, 'from', lastUser, lastAI);
                if (expressionImg) expressionImg.src = `/images/aurora-expressions/${inferred}.png?t=${Date.now()}`;
            }
            // Add subtle animation
            if (expressionImg) {
                expressionImg.style.opacity = '0.7';
                setTimeout(() => { expressionImg.style.opacity = '1'; }, 150);
            }
        } catch (e) {
            console.warn('Expression update failed', e);
        }
    }

    function sendMessage() {
        const txt = msgInput.value.trim();
        if (!txt) return;
        // Remove empty state
        const empty = document.querySelector('.public-chat-empty');
        if (empty) empty.remove();

        addMessage('user', txt);
        // Fade hello message on first send
        fadeHelloMessage();

        msgInput.value = '';
        msgInput.style.height = 'auto';
        // Simulate AI reply
        simulateReply(txt);
    }

    if (msgInput) {
        msgInput.addEventListener('input', function(){
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        msgInput.addEventListener('keydown', function(e){
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); sendMessageOnline();
            }
        });
    }

    // send button (uses online send)
    if (sendBtn) sendBtn.addEventListener('click', function(e){ e.preventDefault(); sendMessageOnline(); });

    // New: online mode - send to server endpoint
    const publicProvider = document.getElementById('publicAiProvider');

    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'public-chat-message public-incoming typing-indicator';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = `
            <div class="public-message-bubble">
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>
        `;
        messages.appendChild(typingDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    function removeTypingIndicator(){
        const t = document.getElementById('typingIndicator'); if (t) t.remove();
    }

    async function sendMessageOnline() {
        const txt = msgInput.value.trim();
        if (!txt) return;
        const empty = document.querySelector('.public-chat-empty'); if (empty) empty.remove();

        addMessage('user', txt);
        // record user message to conversation history
        conversationHistory.push({ role: 'user', content: txt });
        msgInput.value = '';
        msgInput.style.height = 'auto';

        // Disable input while processing
        msgInput.disabled = true;
        sendBtn.disabled = true;
        publicProvider.disabled = true;

        showTypingIndicator();
        const start = Date.now();

        try {
            const res = await fetch('{{ route('chatbot.public.send') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    message: txt,
                    conversation_history: conversationHistory,
                    provider: publicProvider ? publicProvider.value : 'gemini'
                })
            });

            const data = await res.json();
            const responseTimeMs = Date.now() - start;
            document.getElementById('responseTime').textContent = (responseTimeMs / 1000).toFixed(2) + 's';

            removeTypingIndicator();

            if (data.success) {
                addMessage('ai', data.response || '');

                // compute expression for this assistant message
                let expressionForMessage = null;
                if (data.insights && data.insights.expression) {
                    expressionForMessage = data.insights.expression;
                } else {
                    const lastUser = conversationHistory.slice().reverse().find(m => m.role === 'user');
                    expressionForMessage = detectExpressionClient(lastUser ? lastUser.content : '', data.response || '');
                }

                // push assistant message into conversation history
                conversationHistory.push({ role: 'assistant', content: data.response, expression: expressionForMessage });

                // Ensure insights object exists and include computed expression as fallback
                data.insights = data.insights || {};
                if (!data.insights.expression && expressionForMessage) data.insights.expression = expressionForMessage;

                // Debug logging to trace expressions
                console.log('chatbot public: data.insights ->', data.insights, 'expressionForMessage ->', expressionForMessage);
                console.log('chatbot public: conversationHistory ->', conversationHistory);

                // Fade hello message on first successful reply (guarded)
                fadeHelloMessage();

                // Update insights (which also updates the aurora expression)
                try {
                    // Add small cache-buster when switching images to force reload
                    if (data.insights && data.insights.expression) {
                        const img = document.getElementById('publicAuroraImg');
                        if (img) img.src = `/images/aurora-expressions/${data.insights.expression}.png?t=${Date.now()}`;
                    }
                    updateInsights(data.insights);
                } catch (e) {
                    console.warn('Error updating insights', e);
                }

            } else {
                addMessage('ai', 'Error: ' + (data.error || 'No response'));
            }
        } catch (err) {
            console.error(err);
            removeTypingIndicator();
            addMessage('ai', 'Connection error. Please try again later.');
        } finally {
            msgInput.disabled = false;
            sendBtn.disabled = false;
            if (publicProvider) publicProvider.disabled = false;
            msgInput.focus();
        }
    }


})();

// Ensure background video plays at 0.3x speed (muted autoplay may be blocked on some browsers)
(function(){
    const v = document.getElementById('chatbotBackgroundVideo');
    if (!v) return;
    try {
        v.playbackRate = 0.3;
        // Try to play; ignore promise rejection if autoplay blocked
        const p = v.play();
        if (p && typeof p.catch === 'function') p.catch(() => {});
    } catch (e) {
        console.warn('Could not set background video playbackRate', e);
    }
})();
</script>
@endpush