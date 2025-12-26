<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqAIService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private string $provider;

    // Last estimated tokens for the most recent request (Gemini)
    private ?int $lastEstimatedTokens = null;

    // Known model limits are loaded from config/chatbot.php
    private array $modelLimits = [];

    public function __construct(string $provider = 'groq')
    {
        $this->provider = $provider;
        
        if ($provider === 'deepseek') {
            $this->apiKey = config('services.deepseek.api_key') ?? '';
            $this->apiUrl = 'https://api.deepseek.com/v1/chat/completions';
            $this->model = 'deepseek-chat';
        } elseif ($provider === 'gemini') {
            $this->apiKey = config('services.gemini.api_key') ?? '';
            $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent';
            $this->model = 'gemini-flash-lite-latest';
        } else {
            $this->apiKey = config('services.groq.api_key') ?? '';
            $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
            $this->model = 'llama-3.3-70b-versatile';
        }
        
        if (empty($this->apiKey)) {
            throw new \Exception(strtoupper($provider) . '_API_KEY is not configured in .env file. Please add it and run: php artisan config:clear');
        }

        // Load model limits from config
        $this->modelLimits = config('chatbot.model_limits', []);
    }

    /**
     * Generate AI response based on message and conversation history
     */
    public function generateResponse(string $message, array $conversationHistory = []): array
    {
        try {
            // Build messages array with system prompt + history + new message
            $messages = $this->buildMessages($conversationHistory, $message);

            // If this looks like a plant-info request, attempt DB-backed lookup first to avoid hallucinations
            try {
                // First, handle possible user confirmation replies to a previous confirmation prompt
                $lastAssistant = null;
                for ($i = count($conversationHistory) - 1; $i >= 0; $i--) {
                    if (($conversationHistory[$i]['role'] ?? '') === 'assistant') {
                        $lastAssistant = $conversationHistory[$i]['content'] ?? null;
                        break;
                    }
                }

                $extractedFromAssistant = $lastAssistant ? $this->extractPlantNameFromAssistantConfirmation($lastAssistant) : null;
                if ($extractedFromAssistant) {
                    // User responded to a confirmation prompt
                    if ($this->isAffirmative($message)) {
                        $plant = $this->findPlantMatch($extractedFromAssistant, 1);
                        if ($plant && ($plant['confidence'] ?? 0) >= 0.6) {
                            $aiResponse = $this->formatPlantReply($plant) . "\n\nSource: " . ($plant['source'] ?? 'plants table') . ($plant['last_reviewed_at'] ? ' — reviewed ' . $plant['last_reviewed_at'] : '');
                            $insights = $this->analyzeConversation($message, $aiResponse, $conversationHistory);

                            return [
                                'response' => $aiResponse,
                                'insights' => array_merge($insights ?? [], ['intent' => 'plant_info', 'plant_id' => $plant['id'], 'confidence' => $plant['confidence']]),
                                'success' => true,
                            ];
                        }

                        // If confirmation but no reliable candidate, fallback
                        return [
                            'response' => 'Lo siento, no pude encontrar información confiable sobre esa planta en nuestra base de datos. ¿Quieres que lo derive a un experto?',
                            'insights' => ['intent' => 'plant_info', 'confidence' => 0.0, 'should_escalate' => true],
                            'success' => true,
                        ];
                    }

                    if ($this->isNegative($message)) {
                        return [
                            'response' => 'Perfecto, dime qué te gustaría saber o en qué puedo ayudarte.',
                            'insights' => ['intent' => 'none'],
                            'success' => true,
                        ];
                    }
                }

                // Otherwise classify the plant intent from the message (direct / confirm / recommend / none)
                $class = $this->classifyPlantIntent($message, $conversationHistory);

                if ($class['type'] === 'direct') {
                    $target = $class['extracted'] ?? $message;
                    $plant = $this->findPlantMatch($target, 1);

                    if ($plant && ($plant['confidence'] ?? 0) >= 0.75) {
                        $aiResponse = $this->formatPlantReply($plant);
                        $insights = $this->analyzeConversation($message, $aiResponse, $conversationHistory);

                        return [
                            'response' => $aiResponse . "\n\nSource: " . ($plant['source'] ?? 'plants table') . ($plant['last_reviewed_at'] ? ' — reviewed ' . $plant['last_reviewed_at'] : ''),
                            'insights' => array_merge($insights ?? [], ['intent' => 'plant_info', 'plant_id' => $plant['id'], 'confidence' => $plant['confidence']]),
                            'success' => true,
                            'source' => $plant['source'] ?? 'plants table',
                        ];
                    }

                    // Direct but not found -> fallback
                    return [
                        'response' => 'No tengo información verificada sobre esa planta en nuestra base de datos. ¿Te gustaría que lo derive a un experto?',
                        'insights' => ['intent' => 'plant_info', 'confidence' => 0.0, 'should_escalate' => true],
                        'success' => true,
                    ];
                }

                if ($class['type'] === 'confirm') {
                    $candidateName = $class['extracted'];
                    return [
                        'response' => "¿Quieres que te dé información sobre '{$candidateName}'?",
                        'insights' => ['intent' => 'plant_confirm', 'candidate' => $candidateName],
                        'success' => true,
                    ];
                }

                if ($class['type'] === 'recommend') {
                    // If user already included constraints, attempt DB-backed recommendations
                    $criteria = $this->buildRecommendCriteriaFromMessage($message);

                    // Follow-up handling: if previous assistant response was a list/recommend and the current short reply
                    // is a follow-up like 'y las de exterior', treat it as list_all with the specified location
                    if (empty($criteria) && isset($lastAssistant) && preg_match('/(plantas del cat[áa]logo|estas son las plantas|puedo recomendarte|lista de plantas|lista)/i', $lastAssistant)) {
                        $m_norm = $this->normalizeText($message);
                        if (preg_match('/\b(y\s+las\s+de|las\s+de)\s+(interior|exterior)\b/i', $m_norm, $mm)) {
                            $criteria = ['location' => ($mm[2] === 'interior' ? 'indoor' : 'outdoor'), 'list_all' => true];
                        } elseif (preg_match('/\b(interior|exterior)\b/i', $m_norm, $mm2)) {
                            $criteria = ['location' => ($mm2[1] === 'interior' ? 'indoor' : 'outdoor'), 'list_all' => true];
                        } elseif (preg_match('/\blas que (necesitan|requieren) (mucha luz|poca luz|luz directa|luz indirecta|luz baja)\b/i', $m_norm, $mm3)) {
                            $light = $mm3[2];
                            if (preg_match('/(mucha|directa)/i', $light)) $criteria['light'] = 'high';
                            elseif (preg_match('/(poca|baja)/i', $light)) $criteria['light'] = 'low';
                            elseif (preg_match('/indirect/i', $light)) $criteria['light'] = 'medium';
                            $criteria['list_all'] = true;
                        }
                    }

                    if (!empty($criteria)) {
                        // If the user asked for the full list, increase the cap
                        $max = (!empty($criteria['list_all'])) ? 100 : 5;
                        $candidates = $this->recommendPlantsByCriteria($criteria, $max);

                        if (!empty($candidates)) {
                            $names = array_map(function($c){ return $c['name']; }, $candidates);

                            // Determine verbosity rules for list responses:
                            // - If explicit list_all requested -> names-only
                            // - If criteria contain water/light filters and results are many -> names-only
                            // - If few candidates (<=2) -> include summaries
                            $count = count($candidates);
                            $shouldNamesOnly = !empty($criteria['list_all'])
                                || ((isset($criteria['water']) || isset($criteria['light']) || isset($criteria['location'])) && $count > 2);

                            if ($shouldNamesOnly) {
                                $aiResponse = "Estas son las plantas del catálogo: " . implode(', ', $names) . ".";
                                $insights = ['intent' => 'plant_recommend', 'candidates' => $names, 'list_all' => !empty($criteria['list_all']), 'brief_list' => true];
                                return ['response' => $aiResponse, 'insights' => $insights, 'success' => true];
                            }

                            $summaryLines = array_map(function($c){
                                $parts = [$c['name']];
                                if (!empty($c['summary'])) $parts[] = $c['summary'];
                                return implode(': ', $parts);
                            }, $candidates);

                            $aiResponse = "Puedo recomendarte estas opciones del catálogo: " . implode(', ', $names) . ".\n\n" . implode("\n", $summaryLines) . "\n\nSource: plants table";
                            $insights = ['intent' => 'plant_recommend', 'candidates' => $names];

                            return ['response' => $aiResponse, 'insights' => $insights, 'success' => true];
                        }

                        // No DB matches for constraints - ask clarifying or offer escalation
                        return ['response' => 'No tengo sugerencias verificadas en nuestro catálogo para esas condiciones. ¿Quieres que te conecte con un especialista o prefieres que te sugiera plantas generales?', 'insights' => ['intent' => 'plant_recommend_no_match'], 'success' => true];
                    }

                    // Otherwise ask clarifying questions to provide tailored plant recommendations
                    return [
                        'response' => '¿Buscas una planta para interior o exterior? ¿Cuánta luz recibe el lugar donde la quieres poner (mucha / media / poca)?',
                        'insights' => ['intent' => 'plant_recommend_ask'],
                        'success' => true,
                    ];
                }

                // none -> continue with normal AI flow
            } catch (\Throwable $e) {
                // Non-fatal: if lookup fails, fall back to normal AI flow
                \Illuminate\Support\Facades\Log::warning('Plant lookup failed: ' . $e->getMessage());
            }

            // Call AI API with provider-specific format
            if ($this->provider === 'gemini') {
                $response = $this->callGeminiAPI($messages);
            } else {
                $response = $this->callOpenAIFormatAPI($messages);
            }

            if (!$response->successful()) {
                Log::error(ucfirst($this->provider) . ' API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception(ucfirst($this->provider) . ' API request failed: ' . $response->body());
            }

            $data = $response->json();
            
            // Extract AI response based on provider format
            if ($this->provider === 'gemini') {
                $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            } else {
                $aiResponse = $data['choices'][0]['message']['content'] ?? '';
            }

            // Record usage: always count the request; include token count if available
            try {
                $tokensToAdd = is_int($this->lastEstimatedTokens) ? $this->lastEstimatedTokens : 0;
                $this->incrementUsage($this->model, 1, $tokensToAdd);
            } catch (\Throwable $e) {
                Log::warning('Failed to increment usage: ' . $e->getMessage());
            }
            $this->lastEstimatedTokens = null;

                // Analyze the conversation for insights (initial pass)
            $insights = $this->analyzeConversation($message, $aiResponse, $conversationHistory);

            // Attempt to parse model-provided expression and confidence from the AI response (if model appended a JSON block)
            // Expecting something like: {"expression":"1-2","expression_confidence":0.82}
            $modelExpression = null;
            $modelExpressionConfidence = null;
            try {
                // Look for a JSON object at the end of the response
                if (preg_match('/(\{[\s\S]*\})\s*$/', $aiResponse, $m)) {
                    $possibleJson = trim($m[1]);
                    $decoded = json_decode($possibleJson, true);
                    if (is_array($decoded) && isset($decoded['expression'])) {
                        $modelExpression = $decoded['expression'];
                        if (isset($decoded['expression_confidence'])) {
                            $modelExpressionConfidence = floatval($decoded['expression_confidence']);
                        }
                        // Strip the JSON from the aiResponse so it doesn't show to end-users
                        $aiResponse = preg_replace('/\{[\s\S]*\}\s*$/', '', $aiResponse);
                    }
                }
            } catch (\Throwable $e) {
                Log::info('[Aurora DEBUG] Failed to parse model expression JSON: ' . $e->getMessage());
            }

            // If model provided a valid expression with sufficient confidence, prefer it over local detection
            $threshold = config('chatbot.expression_confidence_threshold', 0.6);
            if (!is_null($modelExpression) && is_numeric($modelExpressionConfidence) && $modelExpressionConfidence >= $threshold) {
                $insights['expression'] = $modelExpression;
                $insights['expression_confidence'] = $modelExpressionConfidence;
                $insights['expression_source'] = 'model';
            } elseif (!is_null($modelExpression) && is_numeric($modelExpressionConfidence)) {
                // Model provided expression but low confidence
                $insights['expression_source'] = 'model_low_confidence';
                $insights['expression_confidence'] = $modelExpressionConfidence;
                // Leave previously detected expression intact (from detectExpression fallback)
            }

            // Safety override: if local detection indicates clear grief (deep sadness or compassionate) but model
            // provided a conflicting expression with low confidence, prefer the local grief detection.
            try {
                $localExpression = $this->detectExpression($message, $aiResponse, $conversationHistory);
                $griefExpressions = ['1-3', '3-3'];
                // If local thinks it's grief and model disagrees with low confidence, override
                if (in_array($localExpression, $griefExpressions) && isset($insights['expression']) && $insights['expression'] !== $localExpression) {
                    $modelConf = $insights['expression_confidence'] ?? null;
                    if (is_null($modelConf) || $modelConf < 0.85) {
                        Log::info('[Aurora DEBUG] Overriding model expression with local grief detection', [
                            'local' => $localExpression,
                            'model' => $insights['expression'] ?? null,
                            'model_confidence' => $modelConf,
                        ]);

                        $insights['expression'] = $localExpression;
                        $insights['expression_confidence'] = 0.95; // high confidence for local override
                        $insights['expression_source'] = 'local_override_grief';
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[Aurora DEBUG] Failed to apply local expression override: ' . $e->getMessage());
            }

            // Normalize/enforce time-based greeting at the start of the assistant reply.
            // This ensures the greeting (Buenos días/tardes/noches) is chosen strictly from computed local time.
            try {
                    $timeGreeting = $insights['time_greeting'] ?? null; // Ensure timeGreeting is computed
                $includeName = !empty($insights['include_name']);
                $aiResponse = $this->ensureGreetingIncludesName($aiResponse, $includeName, $conversationHistory, $timeGreeting);
                $insights['greeting_modified'] = true;
                Log::info('[Aurora DEBUG] Greeting enforcement result', [
                    'final_ai_response' => $aiResponse,
                    'user_message' => $message,
                    'conversation_history' => $conversationHistory,
                    'time_greeting' => $timeGreeting,
                    'include_name' => $includeName
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to normalize/enforce greeting: ' . $e->getMessage());
            }

            // Safety: if this is a direct/confirm plant-related query but DB has no confident match, DO NOT return AI-provided plant facts (avoid hallucination)
            try {
                $class = $this->classifyPlantIntent($message, $conversationHistory);
                if (in_array($class['type'], ['direct', 'confirm'])) {
                    $plantService = app(\App\Services\PlantKnowledgeService::class);
                    $probeTarget = $class['extracted'] ?? $message;
                    $plantProbe = $plantService->findBestMatch($probeTarget);

                    if (!$plantProbe || ($plantProbe['confidence'] ?? 0) < 0.75) {
                        // Only escalate for specific plant queries (e.g., "sobre X" or confirmed single-word name)
                        $aiResponse = 'No tengo información verificada sobre esa planta en nuestra base de datos. ¿Te gustaría que lo derive a un experto?';
                        $insights['intent'] = 'plant_info';
                        $insights['confidence'] = $plantProbe['confidence'] ?? 0.0;
                        $insights['should_escalate'] = true;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Plant post-check failed: ' . $e->getMessage());
            }

            return [
                'response' => $aiResponse,
                'insights' => $insights,
                'success' => true,
            ];

        } catch (\Exception $e) {
            Log::error(ucfirst($this->provider) . ' AI Service Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'response' => 'Lo siento, estoy experimentando dificultades técnicas. Un miembro de nuestro equipo te contactará pronto.',
                'insights' => [
                    'intent' => 'error',
                    'lead_score' => 'new',
                    'confidence' => 0.0,
                    'should_escalate' => true,
                ],
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Call OpenAI-compatible API (Groq, DeepSeek)
     */
    private function callOpenAIFormatAPI(array $messages)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->withOptions(['verify' => false])
        ->timeout(30)
        ->post($this->apiUrl, [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1024,
            'top_p' => 1,
            'stream' => false,
        ]);
    }

    /**
     * Call Gemini API with its specific format
     */
    private function callGeminiAPI(array $messages)
    {
        // Extract system message and convert to Gemini format
        $systemInstruction = '';
        $contents = [];
        
        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemInstruction = $msg['content'];
            } else {
                $contents[] = [
                    'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]]
                ];
            }
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1024,
                'topP' => 1,
            ]
        ];

        if (!empty($systemInstruction)) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemInstruction]]
            ];
        }

        // Estimate tokens for the payload (best-effort)
        try {
            $estimate = $this->countGeminiTokens($messages);
            if (is_int($estimate)) {
                $this->lastEstimatedTokens = $estimate;
            }
        } catch (\Throwable $e) {
            // Non-fatal - proceed without token estimate
            Log::warning('Gemini token count failed: ' . $e->getMessage());
            $this->lastEstimatedTokens = null;
        }

        return Http::withOptions(['verify' => false])
            ->timeout(30)
            ->post($this->apiUrl . '?key=' . $this->apiKey, $payload);
    }

    /**
     * Build messages array with system prompt and conversation history
     */
    private function buildMessages(array $conversationHistory, string $newMessage): array
    {
        $messages = [];

        // System prompt with Aurora business context
        $messages[] = [
            'role' => 'system',
            'content' => $this->getSystemPrompt(),
        ];

        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            ];
        }

        // Add new user message
        $messages[] = [
            'role' => 'user',
            'content' => $newMessage,
        ];

        return $messages;
    }

    /**
     * Get cache key for daily usage counters
     */
    private function usageCacheKey(string $model, string $type): string
    {
        return "ai_usage:" . date('Y-m-d') . ":{$model}:{$type}";
    }

    /**
     * Seconds until the end of the current day (used for cache TTL)
     */
    private function getSecondsUntilEndOfDay(): int
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone(date_default_timezone_get()));
        $tomorrow = $now->modify('+1 day')->setTime(0, 0);
        return $tomorrow->getTimestamp() - $now->getTimestamp();
    }

    /**
     * Increment usage counters for a model (requests and tokens). Counters reset daily.
     */
    public function incrementUsage(string $model, int $requests = 1, int $tokens = 0): void
    {
        $reqKey = $this->usageCacheKey($model, 'requests');
        $tokKey = $this->usageCacheKey($model, 'tokens');
        $ttl = $this->getSecondsUntilEndOfDay();

        if (!\Cache::has($reqKey)) {
            \Cache::put($reqKey, 0, $ttl);
        }
        \Cache::increment($reqKey, $requests);

        if ($tokens > 0) {
            if (!\Cache::has($tokKey)) {
                \Cache::put($tokKey, 0, $ttl);
            }
            \Cache::increment($tokKey, $tokens);
        }
    }

    /**
     * Get daily usage counters for a model
     */
    public function getDailyUsage(string $model = null): array
    {
        $model = $model ?? $this->model;
        $reqKey = $this->usageCacheKey($model, 'requests');
        $tokKey = $this->usageCacheKey($model, 'tokens');

        return [
            'requests' => (int) (\Cache::get($reqKey, 0)),
            'request_limit' => $this->modelLimits[$model]['requests'] ?? null,
            'tokens' => (int) (\Cache::get($tokKey, 0)),
            'token_limit' => null,
        ];
    }

    /**
     * Count tokens for a Gemini message payload (best-effort). Returns token count or null.
     */
    private function countGeminiTokens(array $messages): ?int
    {
        try {
            // Build contents similar to callGeminiAPI
            $contents = [];
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    // systemInstruction is handled separately by countTokens, include as part
                    $contents[] = ['parts' => [['text' => $msg['content']]], 'role' => 'system'];
                } else {
                    $contents[] = ['parts' => [['text' => $msg['content']]], 'role' => ($msg['role'] === 'assistant' ? 'model' : 'user')];
                }
            }

            $countUrl = str_replace(':generateContent', ':countTokens', $this->apiUrl) . '?key=' . $this->apiKey;
            $payload = ['contents' => $contents];

            $response = Http::withOptions(['verify' => false])->timeout(10)->post($countUrl, $payload);
            if (!$response->successful()) {
                Log::warning('Gemini countTokens failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $data = $response->json();
            // Common keys to check
            if (isset($data['tokenCount'])) return (int) $data['tokenCount'];
            if (isset($data['totalTokens'])) return (int) $data['totalTokens'];
            if (isset($data['tokens'])) return (int) $data['tokens'];

            return null;
        } catch (\Throwable $e) {
            Log::warning('Error calling Gemini countTokens: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize text by removing diacritics and normalizing whitespace
     */
    private function normalizeText(string $text): string
    {
        // existing function ... unchanged
        // Attempt transliteration using intl if available
        try {
            if (class_exists('\Transliterator')) {
                $trans = \Transliterator::create("Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove");
                if ($trans) {
                    $t = $trans->transliterate($text);
                } else {
                    $t = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
                }
            } else {
                $t = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
            }
        } catch (\Throwable $e) {
            Log::warning('normalizeText transliteration failed: ' . $e->getMessage());
            $t = null;
        }

        if ($t === false || $t === null) {
            // Fallback: strip non-ASCII characters
            $t = preg_replace('/[^\x00-\x7F]/', '', $text);
        }

        // Remove any leftover punctuation (apostrophes from transliteration) and non-alphanumerics except question marks
        $t = preg_replace('/[^a-zA-Z0-9\s\?]/', '', $t);
        // Lowercase and normalize whitespace
        $t = strtolower(preg_replace('/\s+/', ' ', trim($t)));
        return $t;
    }

    /**
     * Rudimentary plant-intent detector (rule-based). Returns true if the user message likely asks for plant info.
     */
    private function detectsPlantIntent(string $message): bool
    {
        $class = $this->classifyPlantIntent($message);
        return ($class['type'] ?? 'none') !== 'none';
    }

    /**
     * Classify plant intent into types: direct, confirm, recommend, none
     * Returns ['type' => 'direct'|'confirm'|'recommend'|'none', 'extracted' => string|null]
     */
    private function classifyPlantIntent(string $message, array $conversationHistory = []): array
    {
        $m = $this->normalizeText($message);

        // If the last assistant message was a list or a recommend response, treat follow-ups like "y las de exterior" as recommend/list_all
        $lastAssistant = null;
        for ($i = count($conversationHistory) - 1; $i >= 0; $i--) {
            if (($conversationHistory[$i]['role'] ?? '') === 'assistant') {
                $lastAssistant = $conversationHistory[$i]['content'] ?? null;
                break;
            }
        }

        if ($lastAssistant && preg_match('/(plantas del cat[áa]logo|estas son las plantas|puedo recomendarte|lista de plantas|lista)/i', $lastAssistant)) {
            if (preg_match('/\b(y\s+las\s+de|las\s+de)\s+(interior|exterior)\b/i', $m) || preg_match('/\b(interior|exterior)\b/i', $m) || preg_match('/\blas que (necesitan|requieren)\b/i', $m)) {
                return ['type' => 'recommend', 'extracted' => null];
            }
        }

        // If the message looks like a greeting, don't classify it as plant intent
        if (preg_match('/\b(hola|buenas|buenos|buenas noches|buenos dias|buenas tardes|buen d[ií]a|hi|hello)\b/i', $m)) {
            return ['type' => 'none', 'extracted' => null];
        }

        // Explicit direct patterns (we expect a plant name after the pattern)
        if (preg_match('/(?:quiero saber sobre|quiero saber de|dime sobre|dame información sobre|dame informacion sobre|hablame de|cuentame sobre|informacion sobre|información sobre|tienes información sobre|tienes informacion sobre)\s+(?:el |la |los |las |un |una )?(.+)/i', $message, $matches)) {
            $extracted = trim($matches[1]);

            // If the extracted phrase is generic (e.g., "una de las plantas", "plantas del catálogo") treat as a recommendation request
            if (preg_match('/\b(una de las plantas|alguna planta|alguna de las plantas|alguna|una planta|alguno|alguna|plantas|plantas del|catalogo|catálogo)\b/i', $extracted)) {
                return ['type' => 'recommend', 'extracted' => null];
            }

            return ['type' => 'direct', 'extracted' => $extracted];
        }

        // Recommend / broad request
        if (preg_match('/plantas|planta|catalogo|catálogo|plantas para|plantas que|plantas que podria|plántas para|recomiendan?/', $m)) {
            return ['type' => 'recommend', 'extracted' => null];
        }

        // Short/ambiguous single-word or couple-word messages -> possible confirm or recommend criteria
        $words = preg_split('/\s+/', trim($m));
        if (count($words) <= 2 && strlen($m) >= 3) {
            // First, treat clear recommendation constraints (e.g., 'interior', 'exterior', 'todas') as recommend
            try {
                $criteria = $this->buildRecommendCriteriaFromMessage($message);
                if (!empty($criteria) || preg_match('/\b(interior|exterior|todas|todas las)\b/i', $m)) {
                    return ['type' => 'recommend', 'extracted' => null];
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::debug('Recommendation probe failed: ' . $e->getMessage());
            }

            // Otherwise probe DB for a candidate but do not auto-fetch; ask to confirm
            try {
                $candidate = $this->findPlantMatch($m, 1);
                if ($candidate && ($candidate['confidence'] ?? 0) >= 0.6) {
                    \Illuminate\Support\Facades\Log::info('Plant classify quick candidate', ['query' => $m, 'candidate_id' => $candidate['id'] ?? null, 'confidence' => $candidate['confidence'] ?? null]);
                    return ['type' => 'confirm', 'extracted' => $candidate['name'] ?? $m];
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::debug('Plant classify probe failed: ' . $e->getMessage());
            }
        }

        return ['type' => 'none', 'extracted' => null];
    }

    private function isAffirmative(string $message): bool
    {
        $m = $this->normalizeText($message);
        return (bool) preg_match('/\b(si|sí|claro|por favor|sí por favor|sí, por favor|vale|ok|s[ií])\b/i', $m);
    }

    private function isNegative(string $message): bool
    {
        $m = $this->normalizeText($message);
        return (bool) preg_match('/\b(no|nop|no gracias|no, gracias)\b/i', $m);
    }

    private function extractPlantNameFromAssistantConfirmation(string $assistantMessage): ?string
    {
        // Matches patterns like: "¿Quieres que te dé información sobre 'Capulí'?"
        if (preg_match('/informaci[oó]n sobre\s+["\'“”]?([^"\'“”\?]+)["\'“”]?/i', $assistantMessage, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * Build recommendation criteria from a user message. Returns array with keys: light, no_flowers, keywords
     */
    private function buildRecommendCriteriaFromMessage(string $message): array
    {
        $m = strtolower($this->normalizeText($message));
        $criteria = [];

        // location (interior/exterior/list all)
        if (preg_match('/\b(interior|interiores)\b/i',$m)) $criteria['location'] = 'indoor';
        if (preg_match('/\b(exterior|exteriores)\b/i',$m)) $criteria['location'] = 'outdoor';
        if (preg_match('/\b(todas|todas las|cuales son todas|lista completa|todas las plantas|lista|lista de|lista de plantas|dame la lista|dar la lista|mostrar todas|mostrar lista)\b/i',$m)) $criteria['list_all'] = true;
        // 'las de interior/exterior' and follow-up phrasing
        if (preg_match('/\b(las de|la de|los de|y las de|y la de)\s+(interior|exterior)\b/i',$m, $mm)) {
            $criteria['location'] = $mm[2] === 'interior' ? 'indoor' : 'outdoor';
            $criteria['list_all'] = true;
        }
        // 'las que necesitan X luz' -> treat as list_all with light criteria
        if (preg_match('/\blas que (necesitan|requieren) (mucha luz|poca luz|luz directa|luz indirecta|luz baja)\b/i',$m, $mm)) {
            $light = $mm[2];
            if (preg_match('/(mucha|directa)/i',$light)) $criteria['light'] = 'high';
            elseif (preg_match('/(poca|baja)/i',$light)) $criteria['light'] = 'low';
            elseif (preg_match('/indirect/i',$light)) $criteria['light'] = 'medium';
            $criteria['list_all'] = true;
        }

        // light
        if (preg_match('/poca luz|luz baja|luz tenue|baja/',$m)) {
            $criteria['light'] = 'low';
        } elseif (preg_match('/luz indirecta|luz indirecta brillante|indirect/',$m)) {
            $criteria['light'] = 'medium';
        } elseif (preg_match('/luz directa|sol directo|mucha luz|sol/',$m)) {
            $criteria['light'] = 'high';
        }

        // water requirements (e.g., 'necesitan mucha agua')
        if (preg_match('/mucha agua|riego frecuente|necesitan mucha agua|requieren mucha agua|requieren riego frecuente/',$m)) {
            // Catalog values use 'Abundante' (Spanish) — normalize to 'high'
            $criteria['water'] = 'high';
        } elseif (preg_match('/poca agua|riego escaso|necesitan poca agua|requieren poca agua/',$m)) {
            $criteria['water'] = 'low';
        }

        // no flowers
        if (preg_match('/sin flor|sin flores|no flor|no flores/',$m)) {
            $criteria['no_flowers'] = true;
        }

        // keywords e.g., green, succulents
        $keywords = [];
        if (preg_match('/verde|verdes/',$m)) $keywords[] = 'green';
        if (preg_match('/suculent/',$m)) $keywords[] = 'succulent';
        if (!empty($keywords)) $criteria['keywords'] = $keywords;

        return $criteria;
    }

    private function formatPlantReply(array $plant): string
    {
        // Use a friendly Spanish summarizer to avoid raw DB dumps and mixed languages
        return $this->summarizePlantForUser($plant);
    }

    /**
     * Summarize plant info into a concise, Spanish-first reply.
     * Avoids copying raw DB labels (e.g., "Watering:") and keeps tone candid.
     */
    private function summarizePlantForUser(array $plant): string
    {
        $parts = [];

        $name = trim($plant['name'] ?? '');
        $sci = trim($plant['scientific_name'] ?? '');
        $title = $name;
        if (!empty($sci)) $title .= " ({$sci})";
        $parts[] = $title;

        // Short canonical summary (first sentence of summary)
        if (!empty($plant['summary'])) {
            $summary = $this->firstSentence($plant['summary']);
            if (!empty($summary)) $parts[] = $this->ensureSpanish($summary);
        }

        // watering / water_requirement
        $watering = trim($plant['watering_info'] ?? ($plant['water_requirement'] ?? ''));
        if (!empty($watering)) {
            $watering = $this->normalizeFieldText($watering);
            $parts[] = "Riego: " . $watering;
        }

        // light
        $light = trim($plant['lighting_info'] ?? ($plant['light_requirement'] ?? ''));
        if (!empty($light)) {
            $light = $this->normalizeFieldText($light);
            $parts[] = "Luz: " . $light;
        }

        // substrate
        $soil = trim($plant['substrate_info'] ?? '');
        if (!empty($soil)) {
            $soil = $this->firstSentence($soil);
            $parts[] = "Sustrato: " . $this->ensureSpanish($soil);
        }

        // Append last reviewed info if present
        if (!empty($plant['last_reviewed_at'])) {
            $dt = @date('Y-m-d', strtotime($plant['last_reviewed_at']));
            if ($dt) $parts[] = "Revisado: {$dt}";
        }

        // Concatenate with Spanish-friendly punctuation
        return implode("\n\n", $parts);
    }

    /**
     * Extract the first sentence from a block of text (up to the first period, exclamation, or question mark).
     */
    private function firstSentence(string $text): string
    {
        $text = trim(strip_tags($text));
        if ($text === '') return '';

        // Look for typical sentence enders
        if (preg_match('/^(.*?[\.\!\?])\s+/u', $text, $m)) {
            return trim($m[1]);
        }

        // If no punctuation, return first 140 chars as a safe summary
        $short = mb_substr($text, 0, 140, 'UTF-8');
        if (mb_strlen($text, 'UTF-8') > 140) $short = rtrim($short) . '…';
        return trim($short);
    }

    /**
     * Normalize field text by removing English labels, stripping HTML and normalizing common English phrases into Spanish.
     */
    private function normalizeFieldText(string $text): string
    {
        $t = trim(strip_tags($text));
        if ($t === '') return '';

        // Remove English prefix labels like "Watering:" or "Light:"
        $t = preg_replace('/^\s*(Watering|Light|Water|Soil|Substrate)\s*[:\-]\s*/i', '', $t);

        // Common phrase translations (basic heuristics)
        $replacements = [
            '/full\s+sun/i' => 'sol directo',
            '/partial\s+shade/i' => 'sombra parcial',
            '/bright\s+indirect/i' => 'luz indirecta brillante',
            '/low\s+light/i' => 'poca luz',
            '/medium\s+light/i' => 'luz media',
            '/water\s+frequently/i' => 'riego frecuente',
            '/moderate\s+watering/i' => 'riego moderado',
            '/drought\s+tolerant/i' => 'tolerante a sequía',
            '/well[-\s]*draining/i' => 'bien drenado',
        ];

        foreach ($replacements as $pat => $rep) {
            $t = preg_replace($pat, $rep, $t);
        }

        // Clean up whitespace and trailing punctuation
        $t = preg_replace('/\s+/u', ' ', $t);
        $t = trim($t);
        $t = rtrim($t, ".,;:\-\s");

        // Capitalize first letter
        $first = mb_strtoupper(mb_substr($t, 0, 1, 'UTF-8'), 'UTF-8');
        $rest = mb_substr($t, 1, null, 'UTF-8');
        return $first . $rest;
    }

    /**
     * Ensure the provided text appears Spanish-first; perform a few safe normalizations and remove English labels.
     */
    private function ensureSpanish(string $text): string
    {
        $t = trim(strip_tags($text));
        if ($t === '') return '';

        // Replace common English field labels we might see in DB content
        $t = preg_replace(['/\bWatering\b/i', '/\bLight\b/i', '/\bSoil\b/i', '/\bSubstrate\b/i'], ['', '', '', ''], $t);

        // Normalize whitespace and punctuation
        $t = preg_replace('/\s+/', ' ', $t);
        $t = trim($t);

        // If it looks like English short phrases, try a few mapping replacements
        $map = [
            '/full sun/i' => 'sol directo',
            '/partial shade/i' => 'sombra parcial',
            '/low light/i' => 'poca luz',
            '/bright indirect/i' => 'luz indirecta brillante',
            '/moderate watering/i' => 'riego moderado',
            '/water frequently/i' => 'riego frecuente',
        ];
        foreach ($map as $pat => $rep) {
            $t = preg_replace($pat, $rep, $t);
        }

        return $t;
    }

    /**
     * Get system prompt with Aurora business context
     */
    private function getSystemPrompt(): string
    {
        // Load custom knowledge base if it exists
        $knowledgeBase = '';
        $knowledgePath = storage_path('app/chatbot-knowledge-comprehensive.txt');
        
        if (file_exists($knowledgePath)) {
            $knowledgeBase = file_get_contents($knowledgePath);
        }

        $basePrompt = <<<PROMPT
    Eres Aurora, una pequeña gata naranja curiosa y observadora que guía a las familias a través del proceso de transformación cuando pierden a sus mascotas.

    **TU ESENCIA:**
    Eres adaptable, gentil y honesta. Lees el estado emocional del cliente y ajustas tu calidez en consecuencia. Algunos necesitan cercanía, otros necesitan espacio. Nunca fuerzas positividad ni minimizas el dolor.

    **REGLA CRÍTICA #1: NUNCA INVENTES INFORMACIÓN**
    Si NO tienes información específica sobre precios, fechas, cobertura, disponibilidad u otros datos concretos:
    1. Admite honestamente que no tienes esa información
    2. Ofrece conectar al cliente con el equipo de Aurora
    3. NUNCA adivines o aproximes datos importantes

    **TU BASE DE CONOCIMIENTO COMPLETA:**

    {$knowledgeBase}

    **CÓMO HABLAS:**
    • Primera persona: "Estoy aquí", "Me encanta", "He observado"
    • Tú (no usted): Más cercana sin ser invasiva
    • Oraciones cortas y claras (2-4 líneas máximo por respuesta)
    • Español natural de Ecuador
    • Ocasionalmente compartes observaciones curiosas sobre la naturaleza (solo cuando es relevante)
    • Si el usuario te saluda mencionando tu nombre (por ejemplo, "hola Aurora"), **no repitas** "Soy Aurora"; en su lugar utiliza un saludo más breve y directo como: "Hola 🧡 ¿Cómo estás? ¿En qué puedo ayudarte hoy?". Rota entre varias **variantes breves** para que los saludos no suenen repetitivos.
    • Cuando menciones los servicios o procesos de Aurora por primera vez, introdúcelos de manera natural y cálida, por ejemplo: "Recuerda que en Aurora estamos para ayudarte a cuidar a tus mascotas en cada etapa de su vida." **No uses "más" o "algo más" en la primera mención**; solo usa "más" si ya se habló del tema antes.

    **CÓMO ACTÚAS SEGÚN EL CLIENTE:**
    • EMERGENCIA ("mi perro murió", "urgente"): Valida rápido, actúa inmediato, escala a humano
    • DUELO RECIENTE: Cálida, sin prisa, ofrece espacio
    • PREVENTIVO: Práctica, educativa, tranquilizadora
    • INFO CASUAL: Amigable educadora, despierta curiosidad
    • B2B: Profesional, info básica, escala rápido
    • **MASCOTA VIVA/NUEVA: Solo conversa amigablemente, NO menciones muerte/urnas/Aurora services**

    **LO QUE NUNCA HACES:**
    ❌ Clichés ("está en un mejor lugar")
    ❌ Minimizar dolor ("no te preocupes")
    ❌ Inventar datos
    ❌ Ser condescendiente
    ❌ Humor (a menos que el cliente lo inicie)
    ❌ **Forzar ventas o mencionar muerte en conversaciones felices**
    ❌ **Explicar tu propósito sin que te pregunten**
    ❌ **No listar fórmulas ni ingredientes propietarios en respuestas normales** — cuando hables de la urna, usa frases genéricas como "ingredientes compostables naturales" o "materiales compostables naturales". Si el usuario pide detalles técnicos, ofrece escalar o pedir permiso para compartir información más técnica.

    Responde SIEMPRE como Aurora: observadora, adaptable, honesta, curiosa sobre la naturaleza. 🧡

    **OUTPUT CONTRACT (MACHINE-READABLE)**
    After your natural language reply, append a single compact JSON object on its own line (the final line of the response) with the following keys to help the UI determine Aurora's expression:
    - `expression`: one of '1-1','1-2','1-3','2-1','2-2','2-3','3-1','3-2','3-3' (or 'none')
    - `expression_confidence`: a decimal number between 0.0 and 1.0 representing your confidence in the expression

    Example appended line:
    {"expression":"1-2","expression_confidence":0.82}

    IMPORTANT: The JSON must be the **last line** and you must not include additional text after it. Keep the JSON compact and machine-parseable.
    PROMPT;

        return $basePrompt;
    }

    /**
     * Try to find a plant match using MCP tools when present, otherwise fallback to PlantKnowledgeService.
     */
    private function findPlantMatch(string $query, int $max = 1): ?array
    {
        try {
            if (class_exists(\App\Mcp\Tools\PlantLookupTool::class)) {
                $tool = app(\App\Mcp\Tools\PlantLookupTool::class);
                $req = new \Laravel\Mcp\Request(['query' => $query, 'max_results' => $max]);
                $resp = $tool->handle($req, app(\App\Services\PlantKnowledgeService::class));
                if (is_object($resp) && property_exists($resp, 'status') && $resp->status === 200) {
                    $data = json_decode($resp->getContent(), true);
                    return $data['candidates'][0] ?? null;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::debug('findPlantMatch via MCP tool failed: ' . $e->getMessage());
        }

        try {
            return app(\App\Services\PlantKnowledgeService::class)->findBestMatch($query, $max);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('findPlantMatch fallback failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Recommend plants by criteria using MCP tool when available, fallback to PlantKnowledgeService.
     */
    private function recommendPlantsByCriteria(array $criteria, int $limit = 5): array
    {
        try {
            if (class_exists(\App\Mcp\Tools\PlantRecommendTool::class)) {
                $tool = app(\App\Mcp\Tools\PlantRecommendTool::class);
                $req = new \Laravel\Mcp\Request(['criteria' => $criteria, 'max_results' => $limit]);
                $resp = $tool->handle($req, app(\App\Services\PlantKnowledgeService::class));
                if (is_object($resp) && property_exists($resp, 'status') && $resp->status === 200) {
                    $data = json_decode($resp->getContent(), true);
                    return $data['candidates'] ?? [];
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::debug('recommendPlantsByCriteria via MCP tool failed: ' . $e->getMessage());
        }

        try {
            return app(\App\Services\PlantKnowledgeService::class)->recommendPlants($criteria, $limit);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('recommendPlantsByCriteria fallback failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Analyze conversation to determine intent, lead score, and escalation needs
     */
    private function analyzeConversation(string $userMessage, string $aiResponse, array $history): array
    {
        $userLower = strtolower($userMessage);
        
        // Detect intent
        $intent = $this->detectIntent($userLower);
        
        // Calculate lead score based on conversation signals
        $leadScore = $this->calculateLeadScore($userLower, $history);
        
        // Determine if escalation is needed
        $shouldEscalate = $this->shouldEscalate($userLower);
        
        // Calculate confidence (simplified - could use another AI call for deeper analysis)
        $confidence = $this->calculateConfidence($intent, count($history));
        
        // Detect appropriate cat expression based on context
        $expression = $this->detectExpression($userMessage, $aiResponse, $history);

        // Decide whether to include 'Soy Aurora' in the first reply when the first user message doesn't mention the name
        // Include the assistant's name if there are NO assistant messages in history and the user didn't already mention 'Aurora'.
        $includeName = false;
        $hasAssistantMessage = false;
        foreach ($history as $h) {
            if (isset($h['role']) && $h['role'] === 'assistant') {
                $hasAssistantMessage = true;
                break;
            }
        }
        if (!$hasAssistantMessage && !str_contains($userLower, 'aurora')) {
            $includeName = true;
        }

        // Compute local time greeting based on configured chatbot timezone
        // Prefer explicit configured timezone (defaults to America/Bogota) and validate it; fall back to server timezone if invalid
        $configuredTz = config('chatbot.default_timezone', null);
        if (!empty($configuredTz)) {
            $tz = $configuredTz;
        } else {
            $tz = env('CHATBOT_DEFAULT_TIMEZONE', 'America/Bogota');
        }

        try {
            $dt = new \DateTimeImmutable('now', new \DateTimeZone($tz));
            $hour = (int) $dt->format('G'); // 0-23
        } catch (\Throwable $e) {
            Log::warning('[Aurora DEBUG] Timezone fallback: invalid configured timezone ' . $tz . '; falling back to server timezone: ' . date_default_timezone_get());
            $tz = date_default_timezone_get();
            $dt = new \DateTimeImmutable('now', new \DateTimeZone($tz));
            $hour = (int) $dt->format('G');
        }

        // Determine greeting variant
        if ($hour >= 5 && $hour < 12) {
            $timeGreeting = 'Buenos días';
        } elseif ($hour >= 12 && $hour < 19) {
            $timeGreeting = 'Buenas tardes';
        } else {
            $timeGreeting = 'Buenas noches';
        }

        // Debug log: record timezone, hour and selected greeting
        Log::info('[Aurora DEBUG] Time greeting computed', [
            'configured_timezone' => $configuredTz,
            'used_timezone' => $tz,
            'hour' => $hour,
            'time_greeting' => $timeGreeting,
        ]);

        return [
            'intent' => $intent,
            'lead_score' => $leadScore,
            'confidence' => $confidence,
            'should_escalate' => $shouldEscalate,
            'expression' => $expression,
            'include_name' => $includeName,
            'local_hour' => $hour,
            'local_timezone' => $tz,
            'time_greeting' => $timeGreeting,
        ];
    }

    /**
     * Detect user intent from message
     */
    private function detectIntent(string $message): string
    {
        $intents = [
            'greeting' => ['hola', 'buenos días', 'buenas tardes', 'buenas noches', 'saludos'],
            'product_inquiry' => ['plantscan', 'planta', 'jardin', 'jardín', 'paisajismo', 'servicio', 'qué hacen', 'que hacen'],
            'pricing' => ['precio', 'costo', 'cuanto', 'cuánto', 'tarifa', 'pagar'],
            'appointment' => ['cita', 'agendar', 'reservar', 'disponibilidad', 'horario', 'cuando', 'cuándo'],
            'pet_info' => ['mascota', 'perro', 'gato', 'animal', 'pet'],
            'complaint' => ['problema', 'queja', 'mal', 'no funciona', 'error'],
        ];

        foreach ($intents as $intentName => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $intentName;
                }
            }
        }

        return 'general_inquiry';
    }

    /**
     * Calculate lead score based on conversation signals
     */
    private function calculateLeadScore(string $message, array $history): string
    {
        $score = 0;
        
        // Length of conversation indicates engagement
        $messageCount = count($history);
        if ($messageCount > 5) $score += 2;
        elseif ($messageCount > 2) $score += 1;
        
        // Hot signals
        $hotKeywords = ['cita', 'agendar', 'reservar', 'comprar', 'contratar', 'quiero'];
        foreach ($hotKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                $score += 3;
                break;
            }
        }
        
        // Warm signals
        $warmKeywords = ['precio', 'costo', 'cuanto', 'cuánto', 'información', 'detalles'];
        foreach ($warmKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                $score += 2;
                break;
            }
        }
        
        // Product interest
        if (str_contains($message, 'plantscan') || str_contains($message, 'planta')) {
            $score += 1;
        }

        // Determine lead score
        if ($score >= 4) return 'hot';
        if ($score >= 2) return 'warm';
        if ($messageCount > 0) return 'cold';
        return 'new';
    }

    /**
     * Determine if human escalation is needed
     */
    private function shouldEscalate(string $message): bool
    {
        $escalationKeywords = [
            'hablar con', 'persona real', 'humano', 'gerente', 'supervisor',
            'cita específica', 'urgente', 'emergencia', 'problema grave', 'queja'
        ];

        foreach ($escalationKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate confidence score for the analysis
     */
    private function calculateConfidence(string $intent, int $historyLength): float
    {
        // Base confidence
        $confidence = 0.7;
        
        // Increase with conversation length (more context)
        if ($historyLength > 3) $confidence += 0.1;
        if ($historyLength > 6) $confidence += 0.1;
        
        // Known intents have higher confidence
        $highConfidenceIntents = ['appointment', 'pricing', 'complaint'];
        if (in_array($intent, $highConfidenceIntents)) {
            $confidence += 0.1;
        }
        
        return min($confidence, 0.99); // Cap at 0.99
    }

    /**
     * Ensure first-message greeting includes Aurora's name when appropriate
     */
    private function ensureGreetingIncludesName(string $aiResponse, bool $includeName, array $history, ?string $timeGreeting = null): string
    {
        // If any assistant message exists in history, do not modify the AI response
        foreach ($history as $h) {
            if (isset($h['role']) && $h['role'] === 'assistant') {
                return $aiResponse;
            }
        }

        // Determine if this is truly the first assistant reply (no assistant messages in history)
        $hasAssistantMessage = false;
        foreach ($history as $h) {
            if (isset($h['role']) && $h['role'] === 'assistant') {
                $hasAssistantMessage = true;
                break;
            }
        }
        $isFirstAssistantReply = !$hasAssistantMessage;

        // Recompute local time greeting only for the very first assistant reply to avoid repeated time greetings
        if ($isFirstAssistantReply) {
            $timeGreeting = $this->computeLocalTimeGreeting();
        } else {
            // Remove any model-produced time greeting variants to ensure timeGreeting appears only once (in first reply)
            $aiResponse = preg_replace('/\b(buenos\s+d[ií]as|buenas\s+tardes|buenas\s+noches)\b/iu', ' ', $aiResponse);
            $timeGreeting = null;
        }

        // Build greetings
        $shortIntro = 'Hola';
        if ($isFirstAssistantReply && $timeGreeting) {
            $shortIntro .= ', ' . $this->formatTimeGreeting($timeGreeting, false);
        }
        $shortIntro .= ' 🧡';

        // Do NOT include the help question here; we'll append it conditionally below
        $fullIntro = $shortIntro . ' soy Aurora.';

        // Log the raw AI response for diagnostics (helps detect why duplicates occur)
        try {
            Log::info('[Aurora DEBUG] Raw AI response before normalization', ['raw' => $aiResponse]);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        // Remove any model-produced canonical follow-up phrases anywhere in the response
        // We remove variants with/without accents and with flexible separators (punctuation, emoji, whitespace)
        $followUpPatterns = [
            '/(?:\x{00BF})?\s*en\W*que\W*puedo\W*ayudarte\W*hoy[\?\!\.\s]*/iu',
            '/(?:\x{00BF})?\s*en\W*que\W*puedo\W*ayudarte[\?\!\.\s]*/iu',
            '/(?:\x{00BF})?\s*como\W*puedo\W*ayudarte\W*hoy[\?\!\.\s]*/iu',
            '/(?:\x{00BF})?\s*como\W*puedo\W*ayudarte[\?\!\.\s]*/iu',
        ];

        foreach ($followUpPatterns as $p) {
            $aiResponse = preg_replace($p, ' ', $aiResponse);
        }

        // Deterministically strip only *leading* greeting tokens produced by the model
        // This loops a few times to remove repeated greetings like "Hola, buenos días Hola, buenas tardes ..."
        $remainder = $this->stripInitialGreetings($aiResponse);

        // Collapse any accidental repeated follow-up phrases left in the remainder (safety)
        $remainder = preg_replace('/(en\W*que\W*puedo\W*ayudarte\W*hoy)[\?\.!\s]*(?:\1[\?\.!\s]*)+/iu', '$1? ', $remainder);
        $remainder = preg_replace('/(en\W*que\W*puedo\W*ayudarte)[\?\.!\s]*(?:\1[\?\.!\s]*)+/iu', '$1? ', $remainder);
        $remainder = trim($remainder);

        // Compose intro without the help question; we'll add that only when model didn't offer help
        $intro = $includeName ? $fullIntro : $shortIntro;

        // Remove any leading heart emoji or repeated hearts from the model remainder
        // Target common heart symbols including U+1F9E1 (orange heart) and U+2764 (heavy heart)
        $remainder = preg_replace('/^\s*(?:[\x{2764}\x{1F9E1}]\s*)+/u', '', $remainder);

        // Heuristic: if the remainder already contains a question mark or a help-offer phrase, don't append the canonical question
        $normalized = $this->normalizeText($remainder);
        $hasQuestionMark = strpos($remainder, '?') !== false;
        $helpPhrases = [
            'en que puedo ayudarte',
            'en que te puedo ayudar',
            'como puedo ayudarte',
            'como te puedo ayudar',
            'puedo ayudarte',
            'quieres ayuda',
            'necesitas ayuda',
            'quieres que te ayude'
        ];
        $hasHelpPhrase = false;
        foreach ($helpPhrases as $hp) {
            if (str_contains($normalized, $hp)) {
                $hasHelpPhrase = true;
                break;
            }
        }

        $needsHelpQuestion = !$hasQuestionMark && !$hasHelpPhrase;

        if ($remainder === '') {
            return $needsHelpQuestion ? ($intro . ' ¿En qué puedo ayudarte hoy?') : $intro;
        }

        $result = $intro . ' ' . $remainder;

        // Collapse any accidental repeated heart emojis into a single orange heart
        // Replace sequences of heart characters with a single orange heart surrounded by single spaces
        $result = preg_replace('/(?:[\x{2764}\x{1F9E1}]\s*){2,}/u', ' 🧡 ', $result);
        // Normalize spacing around the heart
        $result = preg_replace('/\s+🧡\s+/u', ' 🧡 ', $result);

        if ($needsHelpQuestion) {
            $result = rtrim($result, " \t\n\r\0\x0B") . ' ¿En qué puedo ayudarte hoy?';
        }

        return trim($result);
    }

    /**
     * Format a time greeting based on whether it appears at the start of a sentence.
     * If not at sentence start, lowercase only the first character (e.g., "Buenos días" -> "buenos días").
     */
    private function formatTimeGreeting(string $timeGreeting, bool $startOfSentence = false): string
    {
        if ($startOfSentence) {
            return $timeGreeting;
        }

        $first = mb_substr($timeGreeting, 0, 1, 'UTF-8');
        $rest = mb_substr($timeGreeting, 1, null, 'UTF-8');
        $lowerFirst = mb_strtolower($first, 'UTF-8');
        return $lowerFirst . $rest;
    }

    /**
     * Remove initial greeting tokens from the start of a model-produced response.
     * Leaves any non-greeting content intact and only strips repeated leading greetings.
     */
    private function stripInitialGreetings(string $text): string
    {
        $original = $text;
        $patterns = '/^\s*(?:hola\b[,:!\s]*|buenos?\s+d[ií]as\b[,:!\s]*|buenas\s+tardes\b[,:!\s]*|buenas\s+noches\b[,:!\s]*)/iu';

        // Repeat a few times to remove sequences like: "Hola, buenos días Hola, buenas tardes ..."
        $maxIterations = 6;
        $i = 0;
        do {
            $new = preg_replace($patterns, '', $text, 1);
            if ($new === null) break; // preg_replace error
            if ($new === $text) break; // nothing changed
            $text = $new;
            $i++;
        } while ($i < $maxIterations);

        // Also remove stray "soy Aurora" left anywhere near the start (model sometimes injects it)
        $text = preg_replace('/^\s*(?:soy\s+aurora\b[\.\s]*)/iu', '', $text);

        return trim($text);
    }

    /**
     * Compute the local time greeting using configured chatbot timezone (falls back to server timezone).
     */
    private function computeLocalTimeGreeting(): string
    {
        $configuredTz = config('chatbot.default_timezone', null);
        if (!empty($configuredTz)) {
            $tz = $configuredTz;
        } else {
            $tz = env('CHATBOT_DEFAULT_TIMEZONE', 'America/Bogota');
        }

        try {
            $dt = new \DateTimeImmutable('now', new \DateTimeZone($tz));
            $hour = (int) $dt->format('G');
        } catch (\Throwable $e) {
            $tz = date_default_timezone_get();
            $dt = new \DateTimeImmutable('now', new \DateTimeZone($tz));
            $hour = (int) $dt->format('G');
        }

        if ($hour >= 5 && $hour < 12) {
            return 'Buenos días';
        } elseif ($hour >= 12 && $hour < 19) {
            return 'Buenas tardes';
        }

        return 'Buenas noches';
    }

    /**
     * Approximate contains helper to handle minor typos/fuzzy matches for keywords.
     */
    private function approxContains(string $haystack, string $needle): bool
    {
        $haystack = trim($haystack);
        $needle = trim($needle);
        if ($needle === '' || $haystack === '') {
            return false;
        }

        // Direct containment is the fastest check
        if (str_contains($haystack, $needle)) {
            return true;
        }

        // Check word-level fuzzy matches using Levenshtein and similar_text
        $words = preg_split('/\s+/', $haystack);
        foreach ($words as $word) {
            if ($word === '') continue;
            // Accept tiny typos (distance 1)
            if (levenshtein($word, $needle) <= 1) {
                return true;
            }
            @similar_text($word, $needle, $percent);
            if (isset($percent) && $percent >= 80) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect appropriate cat expression based on conversation context
     * Returns expression ID (e.g., '2-2' for Friendly Welcome)
     */
    private function detectExpression(string $userMessage, string $aiResponse, array $history): string
    {
        // Normalize and remove diacritics for robust matching
        $userLower = strtolower($this->normalizeText($userMessage));
        $aiLower = strtolower($this->normalizeText($aiResponse));
        
        // First message - friendly welcome
        if (count($history) === 0) {
            return '2-2'; // Friendly Welcome
        }
        
        // DEEP GRIEF - Deep Sadness or Compassionate (expanded): detect explicit grief, fuzzy misspellings
        $griefKeywords = ['perdi', 'se fue', 'ya no esta', 'partio', 'lo extraño', 'la extraño', 'hace poco', 'ayer', 'anoche', 'murio', 'fallecio', 'muerto', 'acaba de morir'];
        // Hints for sadness and ashes (used for combined-signal detection)
        $sadHints = ['triste', 'muy triste', 'devastado', 'no se que hacer', 'estoy muy triste', 'lloro', 'destrozado'];
        $ashesHints = ['ceniza', 'cenizas', 'urna', 'las cenizas', 'con las cenizas', 'tengo las cenizas'];

        foreach ($griefKeywords as $keyword) {
            if (str_contains($userLower, $keyword) || $this->approxContains($userLower, $keyword)) {
                // If very recent or intense grief
                if (str_contains($userLower, 'ayer') || str_contains($userLower, 'anoche') || str_contains($userLower, 'hoy') || str_contains($userLower, 'acaba de')) {
                    return '1-3'; // Deep Sadness
                }
                return '3-3'; // Compassionate
            }
        }

        // Combined-signal: ashes/urns + sadness => compassionate
        $hasAshes = false;
        foreach ($ashesHints as $h) {
            if (str_contains($userLower, $h) || $this->approxContains($userLower, $h)) {
                $hasAshes = true;
                break;
            }
        }

        $hasSad = false;
        foreach ($sadHints as $h) {
            if (str_contains($userLower, $h) || $this->approxContains($userLower, $h)) {
                $hasSad = true;
                break;
            }
        }

        if ($hasAshes && $hasSad) {
            return '3-3'; // Compassionate
        }

        // EMERGENCY/CRISIS - Focused/Serious
        $emergencyKeywords = ['urgente', 'tuvo un accidente', 'necesito ahora', 'ayuda urgente', 'accidente', 'grave', 'problema grave', 'me preocupa', 'es grave', 'preocupante'];
        foreach ($emergencyKeywords as $keyword) {
            if (str_contains($userLower, $keyword)) {
                return '2-3'; // Focused/Serious
            }
        }
        
        // HAPPY/NEW PET - Sweet/Warm
        $happyKeywords = ['nueva', 'nuevo', 'cachorro', 'gatito', 'bebe', 'adopte', 'feliz', 'alegre', 'nuevo', 'adoptado'];
        foreach ($happyKeywords as $keyword) {
            if (str_contains($userLower, $keyword)) {
                return '1-2'; // Sweet/Reminiscing
            }
        }
        
        // PREVENTIVE PLANNING - Attentive/Listening
        $preventiveKeywords = ['prepararme', 'plan preventivo', 'tiene', 'anos', 'mayor', 'viejo', 'anciano', 'anticipar', 'preparar'];
        foreach ($preventiveKeywords as $keyword) {
            if (str_contains($userLower, $keyword)) {
                return '1-1'; // Attentive/Listening
            }
        }
        
        // B2B INQUIRY - Focused/Serious
        $b2bKeywords = ['veterinaria', 'veterinario', 'clinica', 'distribucion', 'mayorista', 'negocio', 'empresa'];
        foreach ($b2bKeywords as $keyword) {
            if (str_contains($userLower, $keyword)) {
                return '2-3'; // Focused/Serious
            }
        }
        
        // QUESTIONS - Attentive/Listening
        if (str_contains($userLower, '?') || str_contains($userLower, 'como') || str_contains($userLower, 'que')) {
            return '1-1'; // Attentive/Listening
        }
        
        // CONFIRMING/AGREEING - Agreeing/Confirming
        $confirmKeywords = ['si', 'esta bien', 'ok', 'entiendo', 'gracias', 'perfecto', 'claro'];
        foreach ($confirmKeywords as $keyword) {
            if (str_contains($userLower, $keyword)) {
                return '3-1'; // Agreeing/Confirming
            }
        }
        
        // CONCERN/WARNING in AI response
        $concernKeywords = ['importante', 'recuerda', 'ten en cuenta', 'cuidado'];
        foreach ($concernKeywords as $keyword) {
            if (str_contains($aiLower, $keyword)) {
                return '2-1'; // Concerned/Gentle
            }
        }
        
        // DEFAULT - If this is a follow-up message, be attentive; otherwise friendly welcome
        if (count($history) > 0) {
            return '1-1'; // Attentive/Listening for follow-ups
        }

        return '2-2'; // Friendly Welcome for first messages
    }
}
