<?php

namespace Tests\Unit;

use App\Mcp\Resources\KnowledgeResource;
use PHPUnit\Framework\TestCase;

class KnowledgeResourceTest extends TestCase
{
    public function test_fetch_returns_content_and_metadata()
    {
        // Create a temporary knowledge file and pass its path to the resource to avoid depending on app() helpers
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_chatbot_knowledge.txt';
        file_put_contents($tmp, "aurora knowledge test content");

        $resource = new KnowledgeResource($tmp);

        $data = $resource->fetch();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('content', $data);
        $this->assertArrayHasKey('mimeType', $data);
        $this->assertArrayHasKey('uri', $data);
        $this->assertArrayHasKey('priority', $data);
        $this->assertArrayHasKey('lastModified', $data);

        $this->assertIsFloat($data['priority']);
        $this->assertIsString($data['mimeType']);
        $this->assertIsString($data['uri']);

        $this->assertIsString($data['content']);
        $this->assertStringContainsString('aurora knowledge test content', $data['content']);
        $this->assertIsInt($data['lastModified']);

        @unlink($tmp);
    }
}
