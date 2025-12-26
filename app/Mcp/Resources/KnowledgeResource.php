<?php

namespace App\Mcp\Resources;

class KnowledgeResource
{
    /**
     * Path to the knowledge file.
     *
     * @var string
     */
    protected string $path;

    /**
     * Priority / metadata for the resource.
     *
     * @var float
     */
    protected float $priority = 0.95;

    public function __construct(string $path = null)
    {
        $this->path = $path ?? storage_path('app/chatbot-knowledge-comprehensive.txt');
    }

    /**
     * Fetch the knowledge content and metadata.
     *
     * @return array{content:string, mimeType:string, uri:string, priority:float, lastModified:int|null}
     */
    public function fetch(): array
    {
        if (!file_exists($this->path)) {
            return [
                'content' => '',
                'mimeType' => 'text/plain',
                'uri' => 'aurora://resources/knowledge',
                'priority' => $this->priority,
                'lastModified' => null,
            ];
        }

        $content = file_get_contents($this->path);
        $last = filemtime($this->path);

        return [
            'content' => $content,
            'mimeType' => 'text/markdown',
            'uri' => 'aurora://resources/knowledge',
            'priority' => $this->priority,
            'lastModified' => $last,
        ];
    }
}
