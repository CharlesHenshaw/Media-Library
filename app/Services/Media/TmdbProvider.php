<?php

namespace App\Services\Media;

use App\Enums\MediaType;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class TmdbProvider implements MediaProvider
{
    private const IMAGE_BASE = 'https://image.tmdb.org/t/p/w342';

    public function __construct(private MediaType $type, private ?string $token) {}

    public function search(string $query): Collection
    {
        $results = $this->client()
            ->get('/search/'.$this->endpoint(), ['query' => $query])
            ->throw()
            ->json('results', []);

        return collect($results)->map(fn (array $item) => $this->map($item));
    }

    public function find(string $externalId): ?SearchResult
    {
        $response = $this->client()->get('/'.$this->endpoint().'/'.rawurlencode($externalId));

        if ($response->notFound()) {
            return null;
        }

        $item = $response->throw()->json();

        return $this->map($item, collect($item['created_by'] ?? [])->pluck('name')->first());
    }

    private function map(array $item, ?string $creator = null): SearchResult
    {
        return new SearchResult(
            type: $this->type,
            source: 'tmdb',
            externalId: (string) $item['id'],
            title: $item['title'] ?? $item['name'] ?? 'Untitled',
            year: SearchResult::yearFrom($item['release_date'] ?? $item['first_air_date'] ?? null),
            coverUrl: ! empty($item['poster_path']) ? self::IMAGE_BASE.$item['poster_path'] : null,
            creator: $creator,
        );
    }

    private function endpoint(): string
    {
        return $this->type === MediaType::Tv ? 'tv' : 'movie';
    }

    private function client(): PendingRequest
    {
        if (! $this->token) {
            throw new MediaProviderException('TMDB_TOKEN is not configured.');
        }

        return Http::baseUrl('https://api.themoviedb.org/3')
            ->withToken($this->token)
            ->acceptJson()
            ->timeout(10);
    }
}
