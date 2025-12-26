<?php

namespace Tests\Unit;

use App\Mcp\Prompts\AuroraPrompt;
use PHPUnit\Framework\TestCase;

class AuroraPromptTest extends TestCase
{
    public function test_build_includes_timezone_and_name_rule()
    {
        $prompt = new AuroraPrompt();

        $text1 = $prompt->build(['include_name' => true, 'timezone' => 'UTC']);
        $this->assertStringContainsString('Include the user', $text1);
        $this->assertStringContainsString('Timezone for localizations: UTC', $text1);

        $text2 = $prompt->build(['include_name' => false, 'timezone' => 'America/Los_Angeles']);
        $this->assertStringContainsString('Do NOT include the user', $text2);
        $this->assertStringContainsString('Timezone for localizations: America/Los_Angeles', $text2);
    }

    public function test_check_response_for_forbidden_phrases()
    {
        $prompt = new AuroraPrompt();
        $response = "I think this plant likes medium light, but I'm not sure.";

        $found = $prompt->checkResponseForForbiddenPhrases($response);
        $this->assertIsArray($found);
        $this->assertContains('i think', $found);
        $this->assertContains('not sure', $found);
    }
}
