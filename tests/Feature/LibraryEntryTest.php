<?php

namespace Tests\Feature;

use App\Enums\EntryStatus;
use App\Enums\MediaType;
use App\Models\LibraryEntry;
use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LibraryEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.tmdb.token' => 'tmdb-token']);
    }

    private function fakeTmdbMovie(): void
    {
        Http::fake(['api.themoviedb.org/3/movie/603' => Http::response([
            'id' => 603, 'title' => 'The Matrix', 'release_date' => '1999-03-30', 'poster_path' => '/matrix.jpg',
        ])]);
    }

    public function test_guests_cannot_view_the_library()
    {
        $this->get('/library')->assertRedirect('/login');
    }

    public function test_adding_an_entry_fetches_metadata_and_creates_the_media_item()
    {
        $this->fakeTmdbMovie();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/library', [
                'type' => 'film',
                'external_id' => '603',
                'title' => 'Spoofed title is ignored',
                'status' => 'finished',
                'rating' => 5,
                'finished_on' => '2026-01-02',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $item = MediaItem::sole();
        $this->assertSame('The Matrix', $item->title);
        $this->assertSame(1999, $item->year);
        $this->assertSame('https://image.tmdb.org/t/p/w342/matrix.jpg', $item->cover_url);

        $entry = $user->libraryEntries()->sole();
        $this->assertSame(EntryStatus::Finished, $entry->status);
        $this->assertSame(5, $entry->rating);
        $this->assertSame('2026-01-02', $entry->finished_on->toDateString());
    }

    public function test_adding_an_existing_item_reuses_it_without_calling_the_api()
    {
        Http::fake();
        $item = MediaItem::factory()->create(['external_id' => '603']);

        $this->actingAs(User::factory()->create())
            ->post('/library', ['type' => 'film', 'external_id' => '603', 'status' => 'dnf'])
            ->assertSessionHasNoErrors();

        Http::assertNothingSent();
        $this->assertSame(1, MediaItem::count());
        $this->assertSame($item->id, LibraryEntry::sole()->media_item_id);
    }

    public function test_re_adding_updates_the_existing_entry_instead_of_duplicating()
    {
        $this->fakeTmdbMovie();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/library', ['type' => 'film', 'external_id' => '603', 'status' => 'dnf']);
        $this->actingAs($user)->post('/library', ['type' => 'film', 'external_id' => '603', 'status' => 'finished', 'rating' => 3]);

        $entry = LibraryEntry::sole();
        $this->assertSame(EntryStatus::Finished, $entry->status);
        $this->assertSame(3, $entry->rating);
    }

    public function test_unknown_items_are_rejected()
    {
        Http::fake(['api.themoviedb.org/*' => Http::response([], 404)]);

        $this->actingAs(User::factory()->create())
            ->post('/library', ['type' => 'film', 'external_id' => '1', 'status' => 'finished'])
            ->assertSessionHasErrors('external_id');

        $this->assertSame(0, LibraryEntry::count());
    }

    public function test_rating_must_be_between_one_and_five()
    {
        $user = User::factory()->create();
        $entry = LibraryEntry::factory()->for($user)->create();

        foreach ([0, 6, 'abc'] as $rating) {
            $this->actingAs($user)
                ->patch("/library/{$entry->id}", ['status' => 'finished', 'rating' => $rating])
                ->assertSessionHasErrors('rating');
        }

        $this->actingAs($user)
            ->patch("/library/{$entry->id}", ['status' => 'dnf', 'rating' => null])
            ->assertSessionHasNoErrors();

        $this->assertNull($entry->fresh()->rating);
        $this->assertSame(EntryStatus::Dnf, $entry->fresh()->status);
    }

    public function test_status_must_be_valid()
    {
        $user = User::factory()->create();
        $entry = LibraryEntry::factory()->for($user)->create();

        $this->actingAs($user)
            ->patch("/library/{$entry->id}", ['status' => 'watching'])
            ->assertSessionHasErrors('status');
    }

    public function test_users_cannot_change_or_delete_other_users_entries()
    {
        $entry = LibraryEntry::factory()->create(['rating' => 2]);
        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->patch("/library/{$entry->id}", ['status' => 'finished', 'rating' => 5])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete("/library/{$entry->id}")
            ->assertForbidden();

        $this->assertSame(2, $entry->fresh()->rating);
    }

    public function test_users_can_delete_their_entries()
    {
        $user = User::factory()->create();
        $entry = LibraryEntry::factory()->for($user)->create();

        $this->actingAs($user)->delete("/library/{$entry->id}")->assertRedirect();

        $this->assertModelMissing($entry);
    }

    public function test_library_only_shows_the_users_own_entries()
    {
        $user = User::factory()->create();
        LibraryEntry::factory()->for($user)->count(2)->create();
        LibraryEntry::factory()->count(3)->create();

        $this->actingAs($user)
            ->get('/library')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Library/Index')
                ->has('entries.data', 2)
                ->has('entries.data.0.media.cover_url')
                ->where('counts.film', 2));
    }

    public function test_library_can_be_filtered_by_type_and_status()
    {
        $user = User::factory()->create();
        LibraryEntry::factory()->for($user)->for(MediaItem::factory()->ofType(MediaType::Book))->create(['status' => 'finished']);
        LibraryEntry::factory()->for($user)->for(MediaItem::factory()->ofType(MediaType::Book))->create(['status' => 'dnf']);
        LibraryEntry::factory()->for($user)->for(MediaItem::factory()->ofType(MediaType::Game))->create(['status' => 'dnf']);

        $this->actingAs($user)
            ->get('/library?type=book&status=dnf')
            ->assertInertia(fn (Assert $page) => $page
                ->has('entries.data', 1)
                ->where('entries.data.0.media.type', 'book')
                ->where('entries.data.0.status', 'dnf')
                ->where('counts.book', 2)
                ->where('counts.game', 1));
    }

    public function test_library_can_be_sorted_by_rating_and_title()
    {
        $user = User::factory()->create();
        LibraryEntry::factory()->for($user)->for(MediaItem::factory()->state(['title' => 'Banana']))->create(['rating' => null]);
        LibraryEntry::factory()->for($user)->for(MediaItem::factory()->state(['title' => 'Apple']))->create(['rating' => 2]);
        LibraryEntry::factory()->for($user)->for(MediaItem::factory()->state(['title' => 'Cherry']))->create(['rating' => 5]);

        $this->actingAs($user)
            ->get('/library?sort=rating')
            ->assertInertia(fn (Assert $page) => $page
                ->where('entries.data.0.media.title', 'Cherry')
                ->where('entries.data.1.media.title', 'Apple')
                ->where('entries.data.2.media.title', 'Banana'));

        $this->actingAs($user)
            ->get('/library?sort=title')
            ->assertInertia(fn (Assert $page) => $page
                ->where('entries.data.0.media.title', 'Apple')
                ->where('entries.data.2.media.title', 'Cherry'));
    }
}
