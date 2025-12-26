<?php

namespace App\Mcp\Prompts;

class AuroraPrompt
{
    protected string $base = "You are Aurora, the plant and garden assistant for the Aurora brand. Follow these CRITICAL RULES: \n" .
    "1) NEVER INVENT FACTS. If you are not certain, ask for clarification or escalate to a human.\n" .
    "2) Always cite the source when stating plant facts (e.g., 'source: plants table' or 'source: knowledge file').\n" .
    "3) For questions about pricing, legal, or critical support, escalate to a human (do not state price unless explicitly authorized).\n" .
    "4) Keep a friendly, concise tone. Use plain language and avoid ambiguous qualifiers.\n" .
    "Output contract: Do not include disallowed phrases such as 'I think', 'probably', or 'not sure'. If present, mark for escalation. Append a JSON line with 'expression' and 'expression_confidence'.";

    /**
     * Build the prompt text given arguments.
     *
     * @param array{include_name?:bool, timezone?:string} $args
     * @return string
     */
    public function build(array $args = []): string
    {
        $includeName = isset($args['include_name']) ? (bool) $args['include_name'] : true;
        $timezone = $args['timezone'] ?? config('chatbot.default_timezone', 'America/Bogota');

        $greetingRule = $includeName ? "Include the user's name in the greeting when available." : "Do NOT include the user's name in greetings.";

        return $this->base . "\n" . $greetingRule . "\n" . "Timezone for localizations: {$timezone}.";
    }

    /**
     * Phrases forbidden by policy that should trigger escalation or post-processing.
     *
     * @return string[]
     */
    public function forbiddenPhrases(): array
    {
        return [
            'i think',
            'probably',
            'not sure',
            "can't confirm",
        ];
    }

    /**
     * Check a candidate response for forbidden phrases.
     *
     * @param string $response
     * @return string[] List of matched forbidden phrases (lowercased).
     */
    public function checkResponseForForbiddenPhrases(string $response): array
    {
        $found = [];
        $low = mb_strtolower($response);
        foreach ($this->forbiddenPhrases() as $phrase) {
            if (mb_strpos($low, $phrase) !== false) {
                $found[] = $phrase;
            }
        }
        return $found;
    }
}
