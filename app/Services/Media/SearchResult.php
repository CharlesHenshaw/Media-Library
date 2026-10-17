<?php

namespace App\Services\Media;

use App\Enums\MediaType;

final readonly class SearchResult
{
    public function __construct(
        public MediaType $type,
        public string $source,
        public string $externalId,
        public string $title,
        public ?int $year = null,
        public ?string $coverUrl = null,
        public ?string $creator = null,
    ) {}

    /**
     * Attributes for creating a MediaItem.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'type' => $this->type,
            'source' => $this->source,
            'external_id' => $this->externalId,
            'title' => $this->title,
            'year' => $this->year,
            'cover_url' => $this->coverUrl,
            'creator' => $this->creator,
        ];
    }

    /**
     * Extract a four-digit year from a date string like "2019-05-01" or "May 2019".
     */
    public static function yearFrom(?string $date): ?int
    {
        return $date && preg_match('/\b(\d{4})\b/', $date, $m) ? (int) $m[1] : null;
    }
}
