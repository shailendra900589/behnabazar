<?php

namespace App\Support\Seo;

class SeoMeta
{
    /** @param  list<string>  $keywords */
    /** @param  list<array<string, mixed>>  $jsonLd */
    public function __construct(
        public string $title,
        public string $description,
        public string $canonical,
        public array $keywords = [],
        public ?string $image = null,
        public string $type = 'website',
        public bool $index = true,
        public bool $follow = true,
        public array $jsonLd = [],
        public ?string $robotsExtra = null,
    ) {}

    public function robotsContent(): string
    {
        $parts = [
            $this->index ? 'index' : 'noindex',
            $this->follow ? 'follow' : 'nofollow',
        ];

        if ($this->robotsExtra) {
            $parts[] = $this->robotsExtra;
        }

        return implode(', ', $parts);
    }

    public function keywordsString(): string
    {
        return implode(', ', array_slice(array_unique($this->keywords), 0, (int) config('seo.max_keywords', 12)));
    }
}
