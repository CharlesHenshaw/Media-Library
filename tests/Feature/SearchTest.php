<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Models\LibraryEntry;
use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_search()
    {
        $this->get('/search')->assertRedirect('/login');
    }

    public function test_empty_search_renders_without_calling_apis()
    {
        Http::fake();

        $this->actingAs(User::factory()->create())
            ->get('/search')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Search')
                ->where('type', 'film')
                ->has('results', 0));

        Http::assertNothingSent();
    }

    public function test_results_are_flagged_when_already_in_the_library()
    {
        Http::fake(['openlibrary.org/search.json*' => Http::response(['docs' => [
            ['key' => '/works/OL1W', 'title' => 'Owned Book'],
            ['key' => '/works/OL2W', 'title' => 'New Book'],
        ]])]);

        $user = User::factory()->create();
        $item = MediaItem::factory()->ofType(MediaType::Book)->create(['external_id' => 'OL1W']);
        LibraryEntry::factory()->for($user)->for($item)->create(['rating' => 4]);

        $this->actingAs($user)
            ->get('/search?type=book&q=book')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Search')
                ->where('type', 'book')
                ->has('results', 2)
                ->where('results.0.media.title', 'Owned Book')
                ->where('results.0.entry.rating', 4)
                ->where('results.1.entry', null));
    }

    public function test_api_errors_are_shown_instead_of_crashing()
    {
        Http::fake(['openlibrary.org/*' => Http::response([], 503)]);

        $this->actingAs(User::factory()->create())
            ->get('/search?type=book&q=dune')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('error', 'The media service is unavailable right now.')
                ->has('results', 0));
    }
}
