<?php

namespace App\Services\Media;

use App\Enums\MediaType;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MediaSearch
{
    public function provider(MediaType $type): MediaProvider
    {
        return match ($type) {
            MediaType::Film, MediaType::Tv => new TmdbProvider($type, config('services.tmdb.token')),
            MediaType::Game => new RawgProvider(config('services.rawg.key')),
            MediaType::Book => new OpenLibraryProvider,
        };
    }

    /**
     * @return Collection<int, SearchResult>
     *
     * @throws MediaProviderException
     */
    public function search(MediaType $type, string $query): Collection
    {
        $key = 'media-search:'.$type->value.':'.md5(mb_strtolower(trim($query)));

        return $this->guard(fn () => Cache::remember(
            $key,
            now()->addHour(),
            fn () => $this->provider($type)->search($query),
        ));
    }

    /**
     * @throws MediaProviderException
     */
    public function find(MediaType $type, string $externalId): ?SearchResult
    {
        return $this->guard(fn () => $this->provider($type)->find($externalId));
    }

    /**
     * Normalise HTTP failures into a single exception type.
     */
    private function guard(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (ConnectionException|RequestException $e) {
            report($e);

            throw new MediaProviderException('The media service is unavailable right now.', previous: $e);
        }
    }
}
