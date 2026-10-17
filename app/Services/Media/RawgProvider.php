<?php

namespace App\Services\Media;

use App\Enums\MediaType;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class RawgProvider implements MediaProvider
{
    public function __construct(private ?string $key) {}

    public function search(string $query): Collection
    {
        $results = $this->client()
            ->get('/games', ['search' => $query, 'page_size' => 20])
            ->throw()
            ->json('results', []);

        return collect($results)->map(fn (array $item) => $this->map($item));
    }

    public function find(string $externalId): ?SearchResult
    {
        $response = $this->client()->get('/games/'.rawurlencode($externalId));

        if ($response->notFound()) {
            return null;
        }

        $item = $response->throw()->json();

        return $this->map($item, collect($item['developers'] ?? [])->pluck('name')->first());
    }

    private function map(array $item, ?string $creator = null): SearchResult
    {
        return new SearchResult(
            type: MediaType::Game,
            source: 'rawg',
            externalId: (string) $item['id'],
            title: $item['name'] ?? 'Untitled',
            year: SearchResult::yearFrom($item['released'] ?? null),
            coverUrl: $item['background_image'] ?? null,
            creator: $creator,
        );
    }

    private function client(): PendingRequest
    {
        if (! $this->key) {
            throw new MediaProviderException('RAWG_KEY is not configured.');
        }

        return Http::baseUrl('https://api.rawg.io/api')
            ->withQueryParameters(['key' => $this->key])
            ->acceptJson()
            ->timeout(10);
    }
}
