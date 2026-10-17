<?php

namespace App\Services\Media;

use Illuminate\Support\Collection;

interface MediaProvider
{
    /**
     * @return Collection<int, SearchResult>
     */
    public function search(string $query): Collection;

    public function find(string $externalId): ?SearchResult;
}
