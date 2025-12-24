# WhatsApp Chatbot Implementation Plan

## System Overview

Aurora's WhatsApp chatbot uses a **two-number system** to provide automated customer support while maintaining human availability for calls and complex inquiries.

### Phone Number Architecture

**Bot Number (WhatsApp Business API)**
- Dedicated WhatsApp Business number for automated messaging
- Handles all customer text conversations through Aurora admin panel
- AI-powered responses via Groq Llama 3.3 70B (FREE)
- No WhatsApp calling capability (by design)
- Customers interact via WhatsApp messages only

**Personal Number (Regular WhatsApp)**
- Aurora employee's personal WhatsApp number
- Receives regular WhatsApp calls and messages
- **Receives notifications from bot** when action needed
- Used for voice calls and complex consultations
- Normal WhatsApp app functionality

### Customer Experience Flow

1. **Customer messages bot number** → AI responds automatically
2. **Hot lead detected** → Bot sends notification to personal number
3. **Customer needs call** → Bot provides personal number for calling
4. **Complex question** → Bot notifies employee, switches to manual mode

---

## Implementation Status

### ✅ Phase 1: Database & Admin UI (COMPLETED)

**Database Tables:**
- `whatsapp_conversations` - Stores conversation metadata
  - `phone_number`, `client_id`, `is_bot_mode`, `lead_score`
  - Auto-links to existing clients by phone match
- `whatsapp_messages` - Stores all messages
  - `direction`, `content`, `sent_by_bot`, `status`

**Admin Interface:**
- Dashboard at `/admin/chatbot` with stats (conversations, unread, hot leads)
- Conversation list with search, lead badges, bot/manual indicators
- Individual conversation view with full message history
- Bot mode toggle, lead score selector, client information display
- Manual message sending capability
- Test data seeded (4 sample conversations)

**Models & Relationships:**
- `WhatsAppConversation` model with client auto-linking
- `WhatsAppMessage` model with status tracking
- Scopes for active, unread, lead scoring queries

---

## 🚧 Phase 2: WhatsApp Business API Integration (IN PROGRESS)

### Setup Requirements

**Meta Developer Account:**
- App created: "Aurora Chatbot"
- Use case: "Connect with customers through WhatsApp"
- Test WABA and phone number auto-generated
- Need to add production phone number after testing

**API Credentials Needed:**
```env
WHATSAPP_PHONE_NUMBER_ID=          # From Meta App Dashboard
WHATSAPP_BUSINESS_ACCOUNT_ID=      # From Meta App Dashboard
WHATSAPP_ACCESS_TOKEN=             # Permanent token (from System User)
WHATSAPP_VERIFY_TOKEN=             # Random string for webhook verification
WHATSAPP_ADMIN_PHONE_NUMBER=       # Personal number for notifications (+593XXXXXXXXX)
```

### Implementation Steps

**1. Complete Meta App Setup**
- [x] Create Meta Developer app
- [ ] Send test `hello_world` message via Graph API Explorer
- [ ] Create System User and generate permanent access token
- [ ] Add production phone number (requires deleting from WhatsApp Business App first)
- [ ] Set up 2-step verification PIN
- [ ] Configure display name and business profile

**2. Create WhatsApp Service** (`app/Services/WhatsAppService.php`)
```php
- sendMessage($phoneNumber, $message) // Send text message
- sendTemplate($phoneNumber, $templateName, $parameters) // Send template
- markAsRead($messageId) // Mark message as read
- validateWebhookSignature($payload, $signature) // Security
```

**3. Configure Webhook Endpoint**
- URL: `https://auroraurn.pet/webhooks/whatsapp`
- Must be publicly accessible (not localhost)
- SSL certificate required (HTTPS)
- Webhook events: `messages`, `message_status`

**4. Create Webhook Controller** (`app/Http/Controllers/WebhookController.php`)
```php
public function handleWhatsAppWebhook(Request $request)
{
    // Verify webhook (GET request)
    // Process incoming messages (POST request)
    // Create/update conversations and messages
    // Trigger AI response if bot_mode enabled
    // Auto-link to existing clients
}
```

**5. Update ChatbotController**
- Replace placeholder TODO comments with actual WhatsApp API calls
- Implement actual message sending via WhatsAppService
- Update message status after API response

---

## 🔜 Phase 3: AI Integration with Groq (PENDING)

### Groq AI Configuration

**Service:** Groq Cloud API (FREE tier available)
**Model:** Llama 3.3 70B Versatile
**API Key:** Required from https://console.groq.com/

```env
GROQ_API_KEY=                      # From Groq Console
GROQ_MODEL=llama-3.3-70b-versatile # Default model
```

### AI Service Implementation (`app/Services/GroqAIService.php`)

- Added DB-backed plant lookup flow: the AI service detects plant-related intents and queries `plants` table via `app/Services/PlantKnowledgeService` to return sourced, confidence-scored answers and avoid hallucinations. A debug endpoint is available at `/admin/chatbot/plant-lookup`.
- **System prompt catalog updated:** removed the inline authoritative plant list from `storage/app/chatbot-knowledge-comprehensive.txt` and replaced it with a clear instruction to treat the `plants` DB as the single source of truth; the catalog entries are now only fallback/illustrative and must not be used as authoritative inventory in user-facing replies.
- **Policy update:** the assistant is forbidden from mentioning or recommending any plant that is not present in the `plants` database. When no match exists for the user's constraints, the assistant must offer a safe fallback or escalate to a specialist.

**Core Functionality:**
```php
- generateResponse($conversationHistory, $clientContext) // Generate AI reply
- detectIntent($message) // Classify customer intent
- extractLeadScore($conversationHistory) // Calculate lead quality
- shouldEscalateToHuman($message) // Detect when manual intervention needed
```

**Context Awareness:**
- Include conversation history (last 10 messages)
- Include client information if linked (pet name, plant, previous orders)
- Aurora business knowledge (services, pricing, locations)
- Spanish language optimization

**Aurora Business Context Prompt:**
```
Aurora es una empresa ecuatoriana de servicios funerarios para mascotas.

SERVICIOS:
1. Urnas Biodegradables - Transforman cenizas en plantas
2. PlantScan - Plantas seguras para mascotas
3. Cremación - Quito, Guayaquil, Cuenca
4. Diseño de jardines personalizados

PRECIOS: [Actualizar con precios reales]
- Urna biodegradable: $X
- Cremación: $X
- PlantScan: $X

UBICACIONES: Quito, Guayaquil, Cuenca
SITIO WEB: https://auroraurn.pet
TELÉFONO PERSONAL: +593XXXXXXXXX (solo para llamadas)

PERSONALIDAD: Empático, profesional, respetuoso con el duelo.
```

### Intent Classification

**Lead Scoring Logic:**
- `hot` - Ready to purchase, asks about pricing/availability
- `warm` - Interested, asks detailed questions
- `cold` - General inquiry, not engaged
- `new` - First message, no context yet

**Escalation Triggers:**
- Customer explicitly requests human contact
- Complex pricing negotiations
- Complaints or sensitive issues
- AI confidence score < 0.7

---

## 📲 Phase 4: Notification System (PENDING)

### Notification Triggers

**1. Hot Lead Created**
```
🔥 LEAD CALIENTE

Cliente: María González
Teléfono: +593991234567
Interés: PlantScan para perro
Último mensaje: "¿Cuánto cuesta y cuándo pueden venir?"

👉 Ver conversación: https://auroraurn.pet/admin/chatbot/conversations/123
```

**2. Manual Mode Required**
```
⚠️ INTERVENCIÓN NECESARIA

Cliente: Carlos Ramírez
Situación: Pregunta compleja sobre precios corporativos
Bot desactivado automáticamente

👉 Responder ahora: https://auroraurn.pet/admin/chatbot/conversations/124
```

**3. First Message (New Conversation)**
```
💬 NUEVO CONTACTO

Teléfono: +593987654321
Primer mensaje: "Hola, necesito información"

👉 Ver conversación: https://auroraurn.pet/admin/chatbot/conversations/125
```

**4. Daily Summary (8 PM)**
```
📊 RESUMEN WHATSAPP - [Fecha]

Conversaciones: 12 nuevas
Leads calientes: 3
Mensajes enviados: 45
Tasa respuesta: 92%

👉 Ver dashboard: https://auroraurn.pet/admin/chatbot
```

### Implementation

**NotificationService** (`app/Services/NotificationService.php`)
```php
- sendHotLeadAlert($conversation) // Send to admin phone
- sendManualModeAlert($conversation) // Urgent notification
- sendNewConversationAlert($conversation) // Optional
- sendDailySummary() // Scheduled job
```

**Scheduled Job** (`app/Console/Commands/SendDailyChatbotSummary.php`)
- Run daily at 8 PM Ecuador time
- Summarize day's activity
- Send via WhatsApp to admin number

---

## 🔐 Phase 5: Security & Production (PENDING)

### Security Measures

**Webhook Signature Verification:**
- Validate all incoming webhook requests
- Use Meta's X-Hub-Signature-256 header
- Reject unsigned/invalid requests

**Rate Limiting:**
- Prevent abuse of webhook endpoint
- Limit API calls to Meta (avoid hitting rate limits)
- Queue messages during high traffic

**Data Privacy:**
- Encrypt sensitive customer data
- GDPR compliance for EU customers (if applicable)
- Customer opt-out mechanism

### Production Checklist

**Before Launch:**
- [ ] Test all message types (text, templates, media)
- [ ] Verify webhook receives messages reliably
- [ ] Test bot mode auto-responses
- [ ] Test manual mode message sending
- [ ] Verify notification system works
- [ ] Test client auto-linking
- [ ] Load test with multiple concurrent conversations
- [ ] Set up monitoring/alerts for API failures
- [ ] Document admin procedures for staff
- [ ] Train staff on Aurora admin panel usage

**Meta App Review (if required):**
- [ ] Submit app for review
- [ ] Provide use case documentation
- [ ] Show privacy policy
- [ ] Demonstrate opt-in process

---

## Configuration Files

### Environment Variables (.env)

```env
# WhatsApp Business API
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_BUSINESS_ACCOUNT_ID=987654321098765
WHATSAPP_ACCESS_TOKEN=EAAJB...
WHATSAPP_VERIFY_TOKEN=your_random_verify_token_here
WHATSAPP_ADMIN_PHONE_NUMBER=+593991234567

# Groq AI
GROQ_API_KEY=gsk_...
GROQ_MODEL=llama-3.3-70b-versatile
GROQ_MAX_TOKENS=1000
GROQ_TEMPERATURE=0.7

# Application
APP_URL=https://auroraurn.pet
QUEUE_CONNECTION=database
```

### Routes (routes/web.php)

```php
// WhatsApp Webhook (exclude from CSRF)
Route::post('/webhooks/whatsapp', [WebhookController::class, 'handleWhatsAppWebhook'])
    ->name('webhooks.whatsapp');
Route::get('/webhooks/whatsapp', [WebhookController::class, 'verifyWhatsAppWebhook'])
    ->name('webhooks.whatsapp.verify');
```

### Middleware Exclusion (bootstrap/app.php)

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->validateCsrfTokens(except: [
        'webhooks/*',
    ]);
})
```

---

## Testing Strategy

### Local Testing

**Use ngrok for webhook testing:**
```bash
ngrok http 8000
# Use ngrok URL in Meta webhook configuration
```

**Test scenarios:**
1. Send message to bot → Verify webhook receives it
2. Bot sends reply → Verify customer receives it
3. Toggle bot mode → Verify behavior changes
4. Update lead score → Verify notifications sent
5. Link to existing client → Verify auto-linking works

### Production Testing

**Soft launch:**
1. Start with bot disabled (manual mode only)
2. Test with internal team members
3. Enable bot for select customers
4. Monitor for 1 week before full rollout
5. Gradually increase automation

---

## Known Limitations

**WhatsApp API Restrictions:**
- Cannot receive WhatsApp calls (use personal number instead)
- 24-hour customer service window for non-template messages
- Message templates require Meta approval (1-2 days)
- Rate limits: 1 message per 6 seconds to same user

**Technical Constraints:**
- Webhook must be publicly accessible (HTTPS required)
- No offline message queue (if server down, messages lost)
- AI responses require internet connection to Groq
- Client auto-linking only works for existing phone numbers

**Business Considerations:**
- Customers may prefer human interaction initially
- AI may misunderstand complex emotional situations
- Requires monitoring for inappropriate AI responses
- Need human backup for bot failures

---

## Future Enhancements

**Phase 6 (Optional):**
- [ ] Rich media support (images, videos, documents)
- [ ] Interactive buttons and lists in messages
- [ ] Message templates for common scenarios
- [ ] Multi-language support (English for tourists)
- [ ] Integration with calendar for appointment booking
- [ ] CRM integration for sales pipeline tracking
- [ ] Analytics dashboard (response times, satisfaction scores)
- [ ] A/B testing for bot responses
- [ ] Voice message transcription
- [ ] Chatbot personality customization

---

## Support & Documentation

**Meta WhatsApp Docs:** https://developers.facebook.com/docs/whatsapp
**Groq AI Docs:** https://console.groq.com/docs
**Aurora Admin Guide:** [Create internal documentation for staff]

**Emergency Contacts:**
- Meta Support: [Developer support portal]
- Groq Support: [support@groq.com]
- Server Admin: [Your hosting provider]

---

## Change Log

**December 19, 2025:**
- Database structure implemented
- Admin UI completed
- Test data seeded
- WhatsApp API setup initiated
- Two-number system architecture defined
- Notification system planned

**Next Session:**
- Complete Meta app testing (send first message)
- Generate permanent access token
- Implement WhatsAppService class
- Set up webhook endpoint
