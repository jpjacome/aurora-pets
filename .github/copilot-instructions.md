# General AI Agent Instructions for Laravel Applications

## CRITICAL OPERATIONAL DIRECTIVE

**ANALYSIS-ONLY MODE BY DEFAULT**: Unless the user explicitly requests implementation or file changes, ONLY provide analysis, suggestions, and recommendations. NEVER modify files, create new files, or implement changes without explicit user instruction.

**DEPLOYMENT & WORKFLOW CLARIFICATION**: All production updates are applied via FTP (we use FileZilla for uploads). The shared online server also provides a terminal that can be used to run commands on the server when needed; confirm access with the user before attempting remote commands. Development work is done completely locally, and there is a copy of the application hosted online (staging/production) where the prepared artifacts (for example, `vendor/`, compiled assets, and changed files) are uploaded via FTP. Always take backups (files + DB) before performing remote operations and never run destructive database reset commands on production.

**READING BLADE AND HTML FILES**: When analyzing Blade or HTML files, focus on understanding the structure, data bindings, and user interactions. Identify how data flows from controllers to views and note any dynamic elements that may require special handling. Find the head section, if there isn't one, find the component present that has it. Find any external css files and js files. Never do any inline styling, use the external file for all styling without exception.

## General Laravel Application Guidelines

### Framework Configuration
```
Laravel Version: 12.x or latest
PHP Version: ^8.2 or compatible
Database: SQLite (development), MySQL/PostgreSQL (production)
Authentication: Laravel Sanctum or Laravel Breeze
Frontend: Blade templates, custom CSS/JS
Testing: PHPUnit
Development Tools: Laravel Telescope, Pulse, Tinker, Pint
```

### Key Dependencies
```json
{
  "production": [
    "laravel/framework",
    "laravel/sanctum"
  ],
  "development": [
    "laravel/telescope",
    "laravel/breeze",
    "laravel/sail"
  ]
}
```

### Core User Management (Example)
```sql
-- users table
- id (bigint, PK)
- name (varchar 255)
- email (varchar 255, unique)
- password (varchar 255, hashed)
- role (enum: admin|editor|regular)
- email_verified_at, remember_token, timestamps
```

### Application Structure

#### Model Architecture
- Use Eloquent ORM for model relationships
- Implement role-based authorization logic
- Use fillable attributes and casts for data integrity

#### Controller Architecture
- Use resource controllers for CRUD operations
- Implement validation and error handling
- Use policies for authorization

#### View Architecture
- Organize views by user role and feature
- Use Blade components for reusable UI
- Follow Laravel conventions for layouts and partials

### Middleware & Security
- Use built-in CSRF protection
- Validate all user input
- Use Eloquent ORM to prevent SQL injection
- Implement role-based authorization using policies
- Hash passwords securely
- Use API authentication (Sanctum/Breeze)

### Route Architecture
- Group routes by access level (public, authenticated, admin)
- Use RESTful resource routes for CRUD
- Apply middleware for role-based protection

### Development Patterns & Conventions
- Use strict typing and PSR-4 autoloading
- Type hint all methods
- Leverage Laravel features (validation, dependency injection)
- Use factories for testing
- Enable timestamps on models
- Use JSON casting for complex data
- Use the global CSS variables defined in `public/css/aurora-general.css` for all styling (colors, typography, spacing, radii, shadows, transitions, z-index). This keeps visual consistency across components and views.

### Admin Styling Guidelines & Best Practices

**CRITICAL CSS Rules for Admin Interface:**

1. **Input Field Width Management**
   - ALWAYS add `box-sizing: border-box;` to inputs with `width: 100%`
   - Without box-sizing, padding and borders make elements overflow their containers
   - Example:
     ```css
     .my-input {
         width: 100%;
         padding: 0.75rem;
         border: 1px solid var(--color-1);
         box-sizing: border-box; /* REQUIRED */
     }
     ```

2. **Color Consistency Rules**
   - **Dark backgrounds** (using `var(--color-2)` - dark green):
     - Text color: `var(--color-3)` (light green)
     - Secondary text: `rgba(220, 255, 214, 0.7)` or `rgba(220, 255, 214, 0.6)`
     - Placeholders: `rgba(220, 255, 214, 0.4)`
     - Borders: `rgba(220, 255, 214, 0.1)` to `rgba(220, 255, 214, 0.2)`
     - Input backgrounds: `rgba(220, 255, 214, 0.05)` or `rgba(0, 0, 0, 0.2)`
     
   - **Light/Orange backgrounds** (using `var(--color-1)` - orange):
     - Text color: `#000` or dark colors for contrast
     - Never use `var(--color-3)` on orange backgrounds
     
   - **Select dropdowns on dark backgrounds**:
     ```css
     select {
         background: rgba(220, 255, 214, 0.05);
         color: var(--color-3);
     }
     select option {
         background: white;
         color: #000; /* Options need dark text */
     }
     ```

3. **Admin Panel Backgrounds**
   - Primary panels/cards: `var(--color-2)` (dark green)
   - Never use plain `white` or `#f8f9fa` for admin panels
   - Use semi-transparent overlays for depth: `rgba(0, 0, 0, 0.1)` to `rgba(0, 0, 0, 0.3)`

4. **Form Elements on Dark Backgrounds**
   - Inputs must have:
     - `background: rgba(220, 255, 214, 0.05);`
     - `color: var(--color-3);`
     - `border: 1px solid var(--color-1);`
   - Focus states:
     - `background: rgba(220, 255, 214, 0.1);`
     - `box-shadow: 0 0 0 3px rgba(254, 141, 44, 0.1);`

5. **Dashboard Stat Cards**
   - Use existing `.dashboard-grid` and `.dashboard-card` classes
   - Never create new card styles - maintain consistency
   - Structure: `.dashboard-card-header` > `.dashboard-card-body` > `.dashboard-stat-value`

**Common Styling Mistakes to Avoid:**
- ❌ Using `width: 100%` without `box-sizing: border-box`
- ❌ Using dark text (`#333`, `#666`, `color: black`) on dark green backgrounds
- ❌ Using `var(--color-3)` text on `var(--color-1)` backgrounds
- ❌ Creating white/light background panels in admin area
- ❌ Forgetting to style placeholder text on dark backgrounds
- ❌ Not styling select dropdown options (they need light backgrounds even on dark selects)

### File Upload Patterns
- Store files in `public/storage` with symbolic links
- Validate file types and sizes
- Clean up files on record deletion

### Testing Infrastructure
- Organize tests into Feature and Unit directories
- Use RefreshDatabase trait for clean test environment
- Use factories for model creation
- Test authentication and authorization logic

### Deployment & Configuration
- Use environment variables for configuration
- Compile assets with Vite or Laravel Mix
- Store images in storage/app/public/

**FTP-first workflow**: If SSH is not available, prepare `vendor/`, compiled frontend assets, and all changed files locally and upload them via FTP (FileZilla). If the hosting control panel or shared server provides a terminal, it may be used to run non-destructive commands (e.g., `php artisan migrate` when necessary) — always confirm permissions and coordinate with the site owner/host. Avoid running any `migrate:fresh`/`migrate:refresh` or other destructive commands on the live server.

### Email Campaign & Webhook System Notes ⚠️

**CRITICAL: Webhook CSRF Exclusion**
- Webhooks MUST be excluded from CSRF verification in `bootstrap/app.php`:
  ```php
  ->withMiddleware(function (Middleware $middleware): void {
      $middleware->validateCsrfTokens(except: [
          'webhooks/*',
      ]);
  })
  ```
- After uploading `bootstrap/app.php`, ALWAYS run: `php artisan optimize:clear` to clear all caches
- Delete `bootstrap/cache/*.php` files if cache persists

**Status Column & Fillable Arrays**
- The `status` column must accept 'clicked' value (not just enum)
- Migration `update_email_messages_status_enum` changes status to `varchar(20)`
- EmailMessage `$fillable` MUST include: `'delivered_at', 'opened_at', 'clicked_at'`
- Without these in fillable, webhook updates will be silently ignored

**Click Tracking: Two Mechanisms**
1. **Brevo Webhooks** (`WebhookController::handleBrevoWebhook`) - External webhook from Brevo
2. **EmailTrackingController** (`click()` method) - Internal redirect tracking

Both MUST update `status='clicked'` AND `clicked_at=now()` for consistency.

**Common Webhook Issues & Solutions**

| Issue | Cause | Solution |
|-------|-------|----------|
| 419 Page Expired | CSRF blocking webhooks | Add `webhooks/*` to CSRF exclusions, clear caches |
| Status stays 'opened' | EmailTrackingController not updating status | Update click() to set `status='clicked'` |
| Timestamps NULL | Columns not in $fillable | Add timestamp columns to EmailMessage $fillable |
| Database rejects 'clicked' | Enum too restrictive | Run migration to change status to varchar(20) |
| Wrong timezone display | UTC vs local time | Set APP_TIMEZONE in config/app.php |
| No webhook logs | Webhook not configured in Brevo | Enable transactional email events in Brevo dashboard |

**Deployment Checklist for Webhook Changes**

1. Upload files:
   - `bootstrap/app.php` (CSRF exclusion)
   - `app/Http/Controllers/WebhookController.php`
   - `app/Http/Controllers/EmailTrackingController.php`
   - `app/Models/EmailMessage.php`
   - `database/migrations/*_update_email_messages_status_enum.php`
   - `config/app.php` (timezone)

2. Run on server:
   ```bash
   php artisan migrate
   php artisan optimize:clear
   ```

3. Test:
   ```bash
   curl -X POST https://yourdomain.com/webhooks/brevo \
     -H "Content-Type: application/json" \
     -d '{"event":"click","email":"test@example.com","messageId":"test123"}'
   ```
   Should return: `{"ok":true}` (not 419 error)

4. Verify logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```
   Should see: "Webhook received from: brevo" when clicking email links

### FTP Deployment Checklist (online/staging/production) 🔧

**Pre-deploy (local, required)**
- Create a full backup of the live site (files + DB) through the host control panel or by requesting a backup from the host. **Always keep a copy** before changes. ✅
- Run locally: `composer install --no-dev --optimize-autoloader` so `vendor/` is ready to upload.
- Build frontend assets locally (e.g., `npm run build` / `pnpm build` / `vite build`) and include `public/build` (or equivalent) in the upload.
- Run tests locally and verify critical flows (email sending, queues, migrations if applicable).
- Create a zip of the current production files (downloaded from host or via control panel) so a quick rollback is available.

**Files / paths to upload (minimum)**
- `vendor/` (updated with new dependencies like `getbrevo/brevo-php`)
- `composer.lock` (recommended)
- `app/` (modified PHP files)
- `config/` (changed service config, e.g., `config/services.php`)
- `public/` (new compiled assets and `og-images/` if applicable)
- `resources/views/` (changed blade templates)
- `bootstrap/cache/` (do NOT upload cached PHP files; instead clear them after upload)

**Upload (via FileZilla/SFTP)**
- Upload the prepared files to the server, preserving file permissions. Prefer SFTP if available for security.
- Do NOT overwrite `.env` via FTP unless you have an exact file to replace—prefer updating `.env` using the host control panel editor or secure SSH editing.

**Post-upload (server-side verification / actions)**
- If you *have* access to the host terminal: run harmless maintenance commands: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:clear`, `php artisan storage:link` (if needed). Run `php artisan migrate --force` only if there are verified, necessary migrations and you have taken DB backups.
- If you *do not* have terminal access: ask the host to run the above commands OR delete stale cache files in `bootstrap/cache/` (e.g., `config.php`, `routes.php`) so Laravel rebuilds them on next request.
- Restart queue workers (Supervisor/Horizon) — ask host to restart if you cannot do this yourself. Verify queues process jobs by dispatching a test job and checking logs/`failed_jobs`.

**Smoke tests & validation**
- Trigger a test email or campaign and check delivery/status in Brevo dashboard and `storage/logs/laravel.log`.
- Manually test core user flows (login, key pages, upload, forms) and verify no JS or asset errors in browser console.

**Rollback plan**
- If rollback needed: re-upload the backed-up site files and restore the DB snapshot. Notify users if downtime or data loss may occur.

**Security & notes**
- Use SFTP when possible, rotate any API keys that were exposed, and never run destructive commands (`migrate:fresh`, `migrate:refresh`) on production.
- If the host provides a terminal, coordinate with them to run commands instead of attempting risky actions via temporary web scripts.


## AI Agent Operational Guidelines

### Analysis Protocol
1. **Request Classification**: Determine intent (debugging, feature, optimization, etc.)
2. **Context Gathering**: Identify relevant models, controllers, views
3. **Relationship Mapping**: Understand data dependencies
4. **Security Assessment**: Check authorization and validation requirements
5. **Suggestion Formation**: Provide specific, actionable recommendations

### Response Framework
```
ANALYSIS ONLY (unless explicitly requested otherwise):
1. Identify the specific Laravel components involved
2. Reference relevant models, relationships, and migrations
3. Consider role-based access control implications  
4. Suggest implementation approach with code examples
5. Highlight potential security, performance, or architectural concerns
6. Provide testing recommendations
7. Suggest deployment considerations if applicable
```

### Code Suggestion Guidelines
- Always include proper type hints and return types
- Use Laravel's built-in features (validation, authorization, etc.)
- Follow the existing project patterns
- Consider the role-based access control system
- Include error handling and user feedback
- Suggest appropriate tests
- Consider performance implications

### Security Checklist for Suggestions
- [ ] Input validation implemented
- [ ] Authorization checks included  
- [ ] CSRF protection considered
- [ ] SQL injection prevention verified
- [ ] XSS prevention implemented
- [ ] File upload security addressed
- [ ] Role-based access enforced

## Common Development Scenarios

### Adding New Features
1. Create/modify models with proper relationships
2. Add validation rules and authorization policies
3. Implement controller logic with proper error handling
4. Create/update views with consistent styling
5. Add appropriate routes with middleware
6. Include comprehensive tests
7. Update documentation

### Debugging Issues
1. Check Laravel logs in storage/logs/
2. Use Telescope for request tracing (if enabled)
3. Verify database relationships and constraints
4. Check middleware and authorization logic
5. Validate input and output data flow
6. Review error handling and user feedback

### Performance Optimization
1. Identify N+1 query problems
2. Implement eager loading where appropriate
3. Add database indexes for frequently queried columns
4. Consider caching for settings and static data
5. Optimize image storage and serving
6. Review and optimize asset compilation

## EXECUTION MANDATE

When responding to user queries:

1. **ANALYZE FIRST**: Always examine the request in context of the project structure
2. **SUGGEST ONLY**: Provide detailed implementation suggestions without making changes
3. **REFERENCE SPECIFICALLY**: Mention exact files, models, and relationships involved
4. **CONSIDER SECURITY**: Always include authorization and validation requirements
5. **PROVIDE CONTEXT**: Explain how suggestions fit into the larger application architecture
6. **INCLUDE TESTING**: Suggest appropriate test coverage for any proposed changes

**REMEMBER**: Default mode is ANALYSIS and SUGGESTIONS only. Implement changes only when explicitly requested by the user.

## Database Schemas & Fields

Document all database tables, fields, and relationships here as you add them to the project. Update this section whenever a new table or field is introduced.

### Clients Database Schema
```
Table: clients
- id (bigint, PK)
- client (string)
- phone (string)
- address (string)
- profile_url (string, nullable)
- pet_name (string)
- pet_species (string)
- gender (string)
- pet_birthday (date)
- pet_breed (string)
- pet_weight (string)
- pet_color (json or array of strings)   # formerly color
- living_space (string)
- pet_characteristics (json or array of strings) # formerly inspiration
- plant_test (string)
- plant (string)      
- plant_description (string)
- created_at (timestamp)
```

### WhatsApp Chatbot Database Schema
```
Table: whatsapp_conversations
- id (bigint, PK)
- phone_number (string, unique) # WhatsApp number with country code
- client_id (bigint, FK to clients.id, nullable)
- contact_name (string, nullable)
- is_bot_mode (boolean, default true)
- lead_score (enum: new, cold, warm, hot, default 'new')
- last_message_at (timestamp, nullable)
- unread_count (integer, default 0)
- is_archived (boolean, default false)
- created_at, updated_at (timestamps)

Table: whatsapp_messages
- id (bigint, PK)
- conversation_id (bigint, FK to whatsapp_conversations.id)
- direction (enum: incoming, outgoing)
- content (text)
- sent_by_bot (boolean, default false)
- status (enum: pending, sent, delivered, read, failed, default 'sent')
- whatsapp_message_id (string, nullable) # WhatsApp's message ID
- metadata (json, nullable) # For media URLs, template info, etc.
- created_at, updated_at (timestamps)

Relationships:
- WhatsAppConversation belongsTo Client (optional)
- WhatsAppConversation hasMany WhatsAppMessage
- WhatsAppMessage belongsTo WhatsAppConversation
- Client hasMany WhatsAppConversation

Key Features:
- Auto-linking: Conversations auto-link to clients by phone number match
- Manual linking: Admin can link conversations to clients
- Bot/Manual mode: Toggle AI responses per conversation
- Lead scoring: Track conversation quality (new/cold/warm/hot)
- Message tracking: Status tracking (sent/delivered/read)
- Archiving: Archive old conversations

Implementation Details & Notes:
- Models & behavior:
  - `WhatsAppConversation` contains helper methods: `autoLinkToClient()`, `markAsRead()`, `incrementUnread()`, scopes for `active()`, `unread()` and `byLeadScore()` and `getDisplayNameAttribute()`.
  - `WhatsAppMessage` stores `direction`, `sent_by_bot`, `status`, `whatsapp_message_id` and `metadata` (cast as `array`). Use scopes `.incoming()`, `.outgoing()`, `.botGenerated()` and convenience methods `isIncoming()`, `isOutgoing()`, `updateStatus()`.

- AI Service (implemented): `App\Services\GroqAIService`
  - Supports providers: **groq**, **gemini**, **deepseek** (selected by passing `provider` to constructor or via test UI).
  - Uses provider-specific request formats (`callOpenAIFormatAPI` for Groq/DeepSeek and `callGeminiAPI` for Gemini).
  - Builds a rich **system prompt** implementing the Aurora persona and the critical rule **"NEVER INVENT INFORMATION"** (see `getSystemPrompt()`).
  - Performs post-processing: greeting normalization (`ensureGreetingIncludesName()`), local time greetings (`computeLocalTimeGreeting()`), expression detection (`detectExpression()`), intent detection (`detectIntent()`), lead scoring, and escalation detection (`shouldEscalate()`).
  - Tracks daily usage via cache keys: `ai_usage:YYYY-MM-DD:{model}:requests` and `ai_usage:YYYY-MM-DD:{model}:tokens` through `incrementUsage()` / `getDailyUsage()`.
  - Token estimation for Gemini is attempted via `countGeminiTokens()` when available; failures are logged but non-fatal.

- Admin UI & controllers:
  - Dashboard & conversation UI: `Admin\ChatbotController@index`, `show`, `sendMessage`, `toggleMode`, `updateLeadScore`, `archive`, `export`.
  - Test UI: `admin/chatbot/test` and `Admin\ChatbotController::testSend` — this calls `GroqAIService::generateResponse()` and returns `response` + `insights` (response time, usage counters, expression, lead score, etc.).
  - Admin comments: `ChatbotAdminComment` model and `Admin\ChatbotAdminCommentController` for storing moderator notes and conversation context.

- Webhooks & Outbound Sending (Current Status / TODOs):
  - **Current**: `WebhookController` presently handles Brevo email webhooks (signature verification implemented for Brevo) but there is **no implemented handler for WhatsApp webhooks in production**.
  - **TODO (Phase 2)**: Implement WhatsApp webhook handling (`handleWhatsAppWebhook`, `verifyWhatsAppWebhook`) and outbound sending via WhatsApp Business API.
    - Add POST `/webhooks/whatsapp` (and GET verify endpoint) to `routes/web.php` and ensure `webhooks/*` is excluded from CSRF verification in `bootstrap/app.php` (see existing guidance for Brevo webhooks).
    - Implement a queued Job (e.g., `SendWhatsAppMessage`) to call Meta Graph API with `WHATSAPP_ACCESS_TOKEN` and update `whatsapp_message_id` + `status` on success/failure.
    - Webhook processing should map incoming WhatsApp messages to `whatsapp_conversations` (auto-create if needed), create `whatsapp_messages` entries, update `last_message_at` and `unread_count`, attempt `autoLinkToClient()`, and if `is_bot_mode` is true trigger the AI flow to generate and store/send a reply.
    - Ensure webhook signature/verification (verify token) and idempotency for retries.

- Required environment & config values (add to `.env` / `config/services.php` / `config/chatbot.php`):
  - WhatsApp: `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_BUSINESS_ACCOUNT_ID`, `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_VERIFY_TOKEN`, `WHATSAPP_ADMIN_PHONE_NUMBER`.
  - AI providers: `SERVICES_GROQ_API_KEY` (or `services.groq.api_key`), `SERVICES_GEMINI_API_KEY`, `SERVICES_DEEPSEEK_API_KEY` (configured in `config/services.php`).
  - Chatbot config: `CHATBOT_DEFAULT_TIMEZONE` (defaults to `America/Bogota`) and `chatbot.model_limits` (limits per model used by `GroqAIService`).

- Tests & Recommended Coverage:
  - Feature tests for: webhook verification and processing, message lifecycle (pending→sent→delivered→read), `autoLinkToClient()` behavior, admin send flow (including queue job dispatch), and admin UI endpoints.
  - Unit tests for: `GroqAIService::analyzeConversation()`, `ensureGreetingIncludesName()` behavior, token counting for Gemini (mock HTTP responses), and usage counters.
  - Existing tests: `tests/Feature/AdminChatbotCommentsTest.php` covers admin comments; expand coverage to include ChatbotController endpoints and webhook flows.

- Admin styling & UI notes:
  - Chatbot admin UI styles live in `public/css/admin-style.css` under the `WHATSAPP CHATBOT ADMIN STYLES` sections. Maintain consistent card layout and action buttons.

- Operational notes / Safety:
  - The system prompt enforces **never inventing data**; escalate to a human when precise data is required (pricing, dates, urgent cases).
  - Use queued jobs for outbound sending and retries to avoid blocking HTTP webhooks.
  - Add monitoring/logging for webhook failures and AI provider errors (e.g., rate limits, auth issues).

- Deployment checklist for Phase 2 (WhatsApp):
  1. Add environment variables above and verify Meta App Dashboard settings.
  2. Implement webhook endpoints + signature verification + idempotency.
  3. Implement outbound `SendWhatsAppMessage` job, update message statuses, and persist provider message IDs.
  4. Add feature tests and run them locally (use `RefreshDatabase`).
  5. On deploy: upload updated code via FTP, clear caches (`php artisan optimize:clear`), and monitor logs for webhook activity.

If you want, I can now: (A) insert a compact summary of these updates into `.github/copilot-instructions.md` (I already prepared the text), or (B) implement the missing webhook handler and send job as a code change with tests. Which would you prefer?
```

### Tests Database Schema
```
Table: tests
- id (bigint, PK)
- email (string)
- - client (string)
- - email (string)
- - pet_name (string, nullable)
- - pet_species (string)
- - gender (string)
- - pet_birthday (date)
- - pet_breed (string)
- - pet_weight (string)
- - pet_color (json or array of strings)   # formerly color
- - living_space (string)
- - pet_characteristics (json or array of strings) # formerly inspiration
- - plant_test (string)
- - plant (string)      
- - plant_description (string)

When adding these tables, update the Model, Controller, and Feature tests accordingly. Ensure use of `RefreshDatabase` in tests and cast `metadata` as `array` in the model.
```