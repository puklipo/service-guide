<?php

namespace App\Articles;

use Spatie\YamlFrontMatter\YamlFrontMatter;
use Spatie\YamlFrontMatter\Document;

final readonly class Article
{
    private Document $document;

    public function __construct(
        string $file,
    ) {
        $this->document = YamlFrontMatter::parseFile($file);
    }

    public function matter(?string $key = null, mixed $default = null): mixed
    {
        return $this->document->matter($key, $default);
    }

    public function body(): string
    {
        return $this->document->body();
    }
}
