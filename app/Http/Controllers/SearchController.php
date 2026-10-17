<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Http\Resources\LibraryEntryResource;
use App\Services\Media\MediaProviderException;
use App\Services\Media\MediaSearch;
use App\Services\Media\SearchResult;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request, MediaSearch $search): Response
    {
        $type = MediaType::tryFrom($request->string('type')) ?? MediaType::Film;
        $query = trim($request->string('q'));
        $results = collect();
        $error = null;

        if (mb_strlen($query) >= 2) {
            try {
                $results = $search->search($type, $query);
            } catch (MediaProviderException $e) {
                $error = $e->getMessage();
            }
        }

        // Match results against what the user already has so the UI can offer "Edit" instead of "Add".
        $existing = $request->user()->libraryEntries()
            ->with('mediaItem')
            ->whereHas('mediaItem', fn ($q) => $q
                ->where('type', $type)
                ->whereIn('external_id', $results->pluck('externalId')))
            ->get()
            ->keyBy('mediaItem.external_id');

        return Inertia::render('Search', [
            'type' => $type,
            'q' => $query,
            'error' => $error,
            'results' => $results->map(fn (SearchResult $result) => [
                'media' => [
                    'type' => $result->type,
                    'external_id' => $result->externalId,
                    'title' => $result->title,
                    'year' => $result->year,
                    'cover_url' => $result->coverUrl,
                    'creator' => $result->creator,
                ],
                'entry' => isset($existing[$result->externalId])
                    ? (new LibraryEntryResource($existing[$result->externalId]))->resolve()
                    : null,
            ])->values(),
        ]);
    }
}
