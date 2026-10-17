<?php

namespace App\Http\Controllers;

use App\Enums\EntryStatus;
use App\Enums\MediaType;
use App\Http\Requests\StoreLibraryEntryRequest;
use App\Http\Requests\UpdateLibraryEntryRequest;
use App\Http\Resources\LibraryEntryResource;
use App\Models\LibraryEntry;
use App\Models\MediaItem;
use App\Services\Media\MediaProviderException;
use App\Services\Media\MediaSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LibraryEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $type = MediaType::tryFrom($request->string('type'));
        $status = EntryStatus::tryFrom($request->string('status'));
        $sort = in_array($request->query('sort'), ['recent', 'rating', 'title']) ? $request->query('sort') : 'recent';

        $entries = $request->user()->libraryEntries()
            ->select('library_entries.*')
            ->join('media_items', 'media_items.id', '=', 'library_entries.media_item_id')
            ->with('mediaItem')
            ->when($type, fn ($q) => $q->where('media_items.type', $type))
            ->when($status, fn ($q) => $q->where('library_entries.status', $status))
            ->when($sort === 'recent', fn ($q) => $q->latest('library_entries.updated_at'))
            ->when($sort === 'rating', fn ($q) => $q->orderByRaw('library_entries.rating is null')->orderByDesc('library_entries.rating'))
            ->when($sort === 'title', fn ($q) => $q->orderBy('media_items.title'))
            ->orderByDesc('library_entries.id')
            ->paginate(24)
            ->withQueryString();

        $counts = $request->user()->libraryEntries()
            ->join('media_items', 'media_items.id', '=', 'library_entries.media_item_id')
            ->selectRaw('media_items.type, count(*) as total')
            ->groupBy('media_items.type')
            ->pluck('total', 'type');

        return Inertia::render('Library/Index', [
            'entries' => LibraryEntryResource::collection($entries),
            'counts' => $counts,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'sort' => $sort,
            ],
        ]);
    }

    public function store(StoreLibraryEntryRequest $request, MediaSearch $search): RedirectResponse
    {
        $type = MediaType::from($request->validated('type'));
        $externalId = $request->validated('external_id');

        $mediaItem = MediaItem::where('type', $type)->where('external_id', $externalId)->first();

        if (! $mediaItem) {
            // Fetch metadata server-side rather than trusting the client, as media items are shared between users.
            try {
                $result = $search->find($type, $externalId);
            } catch (MediaProviderException $e) {
                throw ValidationException::withMessages(['external_id' => $e->getMessage()]);
            }

            if (! $result) {
                throw ValidationException::withMessages(['external_id' => 'That item could not be found.']);
            }

            $mediaItem = MediaItem::firstOrCreate(
                ['source' => $result->source, 'type' => $type, 'external_id' => $result->externalId],
                $result->toAttributes(),
            );
        }

        $entry = $request->user()->libraryEntries()->updateOrCreate(
            ['media_item_id' => $mediaItem->id],
            $request->safe()->only(['status', 'rating', 'finished_on']),
        );

        return back()->with('success', $entry->wasRecentlyCreated
            ? "Added {$mediaItem->title} to your library."
            : "Updated {$mediaItem->title}.");
    }

    public function update(UpdateLibraryEntryRequest $request, LibraryEntry $entry): RedirectResponse
    {
        Gate::authorize('update', $entry);

        $entry->update($request->validated());

        return back()->with('success', "Updated {$entry->mediaItem->title}.");
    }

    public function destroy(LibraryEntry $entry): RedirectResponse
    {
        Gate::authorize('delete', $entry);

        $entry->delete();

        return back()->with('success', "Removed {$entry->mediaItem->title} from your library.");
    }
}
