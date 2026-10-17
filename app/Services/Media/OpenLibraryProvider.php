<?php

namespace App\Services\Media;

use App\Enums\MediaType;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class OpenLibraryProvider implements MediaProvider
{
    private const COVER_BASE = 'https://covers.openlibrary.org/b/id/';

    public function search(string $query): Collection
    {
        return $this->query($query, 20);
    }

    public function find(string $externalId): ?SearchResult
    {
        if (! preg_match('/^OL\d+W$/', $externalId)) {
            return null;
        }

        // Look the work up via the search index so the metadata matches what search returned.
        return $this->query("key:/works/{$externalId}", 1)->first();
    }

    /**
     * @return Collection<int, SearchResult>
     */
    private function query(string $query, int $limit): Collection
    {
        $docs = $this->client()
            ->get('/search.json', [
                'q' => $query,
                'fields' => 'key,title,first_publish_year,author_name,cover_i',
                'limit' => $limit,
            ])
            ->throw()
            ->json('docs', []);

        return collect($docs)->map(fn (array $doc) => new SearchResult(
            type: MediaType::Book,
            source: 'openlibrary',
            externalId: $this->workId($doc['key']),
            title: $doc['title'] ?? 'Untitled',
            year: $doc['first_publish_year'] ?? null,
            coverUrl: $this->coverUrl($doc['cover_i'] ?? null),
            creator: $doc['author_name'][0] ?? null,
        ));
    }

    private function coverUrl(?int $coverId): ?string
    {
        return $coverId ? self::COVER_BASE.$coverId.'-M.jpg' : null;
    }

    private function workId(string $key): string
    {
        return str($key)->afterLast('/')->toString();
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl('https://openlibrary.org')
            ->acceptJson()
            ->timeout(10);
    }
}
