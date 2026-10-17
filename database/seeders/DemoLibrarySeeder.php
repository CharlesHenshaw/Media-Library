<?php

namespace Database\Seeders;

use App\Enums\EntryStatus;
use App\Enums\MediaType;
use App\Models\MediaItem;
use App\Models\User;
use App\Services\Media\MediaSearch;
use Illuminate\Database\Seeder;

/**
 * Creates a demo account with a library of real items fetched from the media APIs.
 * Requires TMDB_TOKEN and RAWG_KEY. Log in as demo@example.com / password.
 */
class DemoLibrarySeeder extends Seeder
{
    /**
     * [type, search query, status, rating]
     */
    private const ITEMS = [
        ['book', 'the hobbit tolkien', 'finished', 5],
        ['book', 'dune frank herbert', 'finished', 4],
        ['book', 'piranesi susanna clarke', 'finished', 5],
        ['book', 'ulysses james joyce', 'dnf', null],
        ['book', 'the name of the wind', 'finished', 4],
        ['film', 'the grand budapest hotel', 'finished', 5],
        ['film', 'spirited away', 'finished', 5],
        ['film', 'tenet', 'dnf', 2],
        ['film', 'arrival', 'finished', 4],
        ['tv', 'the bear', 'finished', 4],
        ['tv', 'severance', 'finished', 5],
        ['tv', 'lost', 'dnf', 3],
        ['game', 'hades', 'finished', 5],
        ['game', 'outer wilds', 'finished', 5],
        ['game', 'elden ring', 'dnf', 4],
        ['game', 'stardew valley', 'finished', 4],
    ];

    public function run(MediaSearch $search): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo Reader', 'password' => 'password', 'email_verified_at' => now()],
        );

        foreach (self::ITEMS as $i => [$type, $query, $status, $rating]) {
            $result = $search->search(MediaType::from($type), $query)->first();

            if (! $result) {
                $this->command?->warn("No result for {$query}");

                continue;
            }

            $item = MediaItem::firstOrCreate(
                ['source' => $result->source, 'type' => $result->type, 'external_id' => $result->externalId],
                $result->toAttributes(),
            );

            $entry = $user->libraryEntries()->updateOrCreate(
                ['media_item_id' => $item->id],
                ['status' => EntryStatus::from($status), 'rating' => $rating, 'finished_on' => now()->subWeeks($i * 2)],
            );

            // Spread entries out so "recently updated" sorting looks natural.
            $entry->timestamps = false;
            $entry->forceFill(['updated_at' => now()->subDays($i), 'created_at' => now()->subDays($i)])->save();
        }
    }
}
