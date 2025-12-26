# MCP Chatbot Implementation & Tracking

Status: In-progress

Latest update (2025-12-26):
- Current step: **Implement PlantLookupTool** — **Completed** ✅
- Owner: GitHub Copilot
- Notes: Implemented `app/Mcp/Tools/PlantLookupTool.php` with input/output schemas, added `PlantLookupTool` to `app/Mcp/Servers/ChatbotServer.php`, and added tests at `tests/Unit/PlantLookupToolTest.php`.
- Tests: Ran `php artisan test --filter=PlantLookupToolTest` locally — **Initially failed** due to missing SQLite PDO driver. I updated the unit tests to mock `PlantKnowledgeService` and added lightweight MCP shims to avoid a hard dependency on `laravel/mcp` so tests run in a minimal local environment.

- Result: After the changes, the `PlantLookupTool` tests now **pass** locally (2 tests, 4 assertions). ✅

- Next step: **Add KnowledgeResource** — **Completed** ✅
  - Implemented `app/Mcp/Resources/KnowledgeResource.php` that reads the canonical knowledge file (`storage/app/chatbot-knowledge-comprehensive.txt`) and exposes metadata (mimeType, uri, priority, lastModified).
  - Added unit test: `tests/Unit/KnowledgeResourceTest.php` (uses a temporary file to avoid app() dependencies). Test run: `php artisan test --filter=KnowledgeResourceTest` — **pass** (note: PHPUnit docblock deprecation warnings in unrelated tests).

- Next step: **Create AuroraPrompt** — **Completed** ✅
  - Implemented `app/Mcp/Prompts/AuroraPrompt.php` with `build()` for include_name/timezone args and `checkResponseForForbiddenPhrases()` for policy checks.
  - Added unit test: `tests/Unit/AuroraPromptTest.php` — Test run: `php artisan test --filter=AuroraPromptTest` — **pass**.

- Next step: **Implement SendWhatsAppMessageTool** — **Completed** ✅
  - Implemented `app/Mcp/Tools/SendWhatsAppMessageTool.php` that accepts `{to, message, dry_run}` and enqueues `App\Jobs\SendWhatsAppMessage` when `dry_run=false`.
  - Added `app/Jobs/SendWhatsAppMessage.php` (placeholder implementation) and added attributes `App\Mcp\Attributes\IsOpenWorld` and `App\Mcp\Attributes\IsIdempotent` for annotation.
  - Added unit test: `tests/Unit/SendWhatsAppMessageToolTest.php` — Test run: `php artisan test --filter=SendWhatsAppMessageToolTest` — **pass**.

- Progress: **Integrate `GroqAIService` with MCP tools** — **In-progress** 🟡
  - Refactored `GroqAIService` to prefer MCP tools for lookups: added private helpers `findPlantMatch()` and `recommendPlantsByCriteria()` that call `PlantLookupTool` / `PlantRecommendTool` when available and fall back to `PlantKnowledgeService`.
  - This reduces duplication and ensures the same logic is used by tools and the service to avoid hallucinations.
  - Completed: **Refine plant Q&A flow (criteria detection, list_all, loop prevention)** — Implemented:
    - `buildRecommendCriteriaFromMessage()` now detects `location` (`interior`/`exterior`), `list_all`, and improved light mapping.
    - `classifyPlantIntent()` now prefers `recommend` when short replies contain recommendation criteria (e.g., `interior`) to prevent confirm loops.
    - `GroqAIService` recommend flow supports `list_all` (returns larger candidate lists and sets `insights.list_all`).
    - `PlantRecommendTool` supports `list_all` (increases cap) and unit tests added.
  - Tests added & passing locally: `tests/Feature/ChatbotRecommendFlowTest.php` (flow reproducing loop fixed) and updated `tests/Unit/PlantRecommendToolTest.php`.
  - Next: add end-to-end & CI inspector checks for forbidden phrases and prompt contract.


Knowledge file review (summary)

- Purpose: Ensure the `chatbot-knowledge-comprehensive.txt` content (personality, safety rules, product & pricing info, escalation rules, language rules, plant catalog authority) is preserved and exposed to MCP clients appropriately.

- Key items to preserve and map to MCP primitives:
  - Identity & Greeting rules (time-based greetings, when to include "soy Aurora", capitalization rules) → **AuroraPrompt** (prompt args: include_name, timezone) and prompt-enforced greeting normalization tests.
  - Non-negotiable behaviors (NEVER invent information, never list proprietary ingredients, escalation for pricing/emergencies/B2B/complaints) → **Prompt + Resource**: include rules in `AuroraPrompt` and add `KnowledgeResource` with explicit "do not invent" metadata; add guard tests ensuring responses escalate instead of inventing.
  - Immediate escalation triggers & adaptive response framework (Level 1/2/Grief/Preventive/B2B) → **Tools & Prompts**: prompts should detect and set intents; tools should return structured `should_escalate` flags; add tests for detection and routing.
  - Product & pricing facts (URN behavior, pricing by weight, service inclusions, delivery times) → Expose as **KnowledgeResource** with stable text and structured metadata (priority, last_modified). For pricing: always escalate to human and never state price unless explicitly allowed; include a `pricing_policy` flag in resource metadata.
  - Plant catalog rules (DB is authoritative; always cite `source: plants table` and include `last_reviewed_at`) → Enforce in **PlantLookupTool/PlantRecommendTool** outputs (already implemented to include `source` and `confidence`) and add unit tests to assert the `source` field.
  - Communication style & language rules (word substitutions, forbidden words, emoji rules, tone) → Centralize in **AuroraPrompt**; add automated tests that validate generated replies avoid forbidden phrases and follow tone constraints.
  - Common Q&As and templates (how to describe urn, planting steps, customer-facing phrases) → Add to **KnowledgeResource** as canonical text and provide a `template` interface for prompts to reference.

- Implementation notes & tests to add:
  - `KnowledgeResource` should expose the raw content and structured metadata: `{ uri: 'aurora://resources/knowledge', mimeType: 'text/markdown', priority: 0.9, lastModified: '2025-12-26' }`.
  - `AuroraPrompt` must embed CRITICAL RULES and the OUTPUT CONTRACT (append JSON expression) and validate args (include_name:boolean, timezone:string).
  - Add unit tests: forbidden-phrases test, greeting-normalization test (various timezones and first/second messages), escalation-detection test (price → should_escalate true), plant-fact-safety test (when DB missing, tool returns escalation suggestion, not model facts).
  - Add an integration test that ensures when `PlantLookupTool` returns `null`, the prompt flow suggests escalation and does not include invented plant facts.
  - Annotate dangerous/external actions (e.g., `SendWhatsAppMessageTool`) with `#[IsOpenWorld]` and `#[IsIdempotent]` where appropriate; add test to simulate dry-run vs live-run.

- Next step: implement `KnowledgeResource` and `AuroraPrompt` (IDs 5 and 6). After that, add the escalation and forbidden-phrases tests mentioned above and re-run the full test suite.


- Notes on approaches:
  - Mocking the service makes unit tests lightweight and DB-independent (fast & CI-friendly).
  - If you prefer integration tests against a real DB, you can still configure a `aurora_test` MySQL DB and run full migrations/seeds for more realistic tests (see steps below).

- If you want the integration option, here are the steps to configure MySQL for tests:

  1. Create local test DB (example):
     - mysql -u root -p
     - CREATE DATABASE aurora_test;
     - CREATE USER 'aurora_test'@'localhost' IDENTIFIED BY 'your_password';
     - GRANT ALL PRIVILEGES ON aurora_test.* TO 'aurora_test'@'localhost';

  2. Update `phpunit.xml` (or create `.env.testing`) to set DB variables for tests:
     - <env name="DB_CONNECTION" value="mysql"/>
     - <env name="DB_DATABASE" value="aurora_test"/>
     - <env name="DB_USERNAME" value="aurora_test"/>
     - <env name="DB_PASSWORD" value="your_password"/>

  3. Run migrations for test DB (artisan test / RefreshDatabase will migrate automatically), or manually run:
     - php artisan migrate --env=testing --database=mysql

  4. Seed minimal data for plants (preferred):
     - Create a `TestPlantsSeeder` that inserts a few representative `plants` rows, then run: php artisan db:seed --class=TestPlantsSeeder --env=testing

  5. Re-run tests: php artisan test --filter=PlantLookupToolTest

- PlantRecommendTool: implemented and unit-tested (2 tests) and both tests pass locally. ✅
- Next steps: implement `KnowledgeResource` (ID 5) and `AuroraPrompt` (ID 6); after that we'll add the SendWhatsAppMessageTool and integrate MCP tools into `GroqAIService`.

This file tracks the implementation of Laravel MCP for the Aurora chatbot. Use it to record progress, bugs, owners, and important decisions.

---

## Overview

Goal: Add a Laravel MCP server to expose structured Tools/Prompts/Resources for the chatbot to reduce hallucinations, centralize persona and knowledge, enable structured actions (WhatsApp sends), and improve testability and security.

Scope:
- Plant info & recommend features
- Knowledge resource
- Message sending tools (WhatsApp)
- Prompts for persona enforcement
- Integration and migration plan for existing `GroqAIService`

---

## Milestones

- Phase 1: Add server skeleton, PlantLookupTool, PlantRecommendTool, KnowledgeResource, AuroraPrompt
- Phase 2: SendWhatsAppMessageTool, webhook integration, GroqAI integration
- Phase 3: CI/Inspector tests, monitoring, staging rollout

---

## Tracking/Todos

(See `docs/MCP-CHATBOT-TODOS.md` or project tracker.)

Current in-progress: Create implementation/tracking file (this file).

---

## Bug & Issue Template

- ID: (auto-generated)
- Title:
- Description: (steps to reproduce, logs, screenshots)
- Impact: (low/medium/high)
- Owner:
- Status: (open/in-progress/resolved)
- PR: (link)
- Notes:

---

## Progress Log

- 2025-12-26: Created implementation file and TODOs. (owner: system)

---

## Deploy / Rollout Checklist

- Add MCP routes: `routes/ai.php` (publish via vendor:publish)
- Register server in `routes/ai.php` (protected by `auth:sanctum` or Passport)
- Add environment variables: (see `WHATSAPP_CHATBOT_IMPLEMENTATION.md` for WhatsApp and Groq keys)
- Add tests and ensure CI runs Inspector tests
- Backup DB + assets before production deploy

---

## Owners & Contacts

- Chatbot/MCP lead: @TODO
- Dev: @TODO
- QA: @TODO

---

## Notes

- Consider using Sanctum for internal tooling, Passport for external MCP clients.
- Tools that perform external actions (WhatsApp send) should carry `#[IsOpenWorld]`.
- All tool inputs/outputs should include `source` and `confidence` where relevant.

---

*If you'd like, I can now create the server/tool skeleton files and example tests for the first two high-priority todos.*