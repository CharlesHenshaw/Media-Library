<?php

namespace Tests\Feature;

use App\Enums\MediaType;
use App\Services\Media\MediaProviderException;
use App\Services\Media\MediaSearch;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediaProvidersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.tmdb.token' => 'tmdb-token', 'services.rawg.key' => 'rawg-key']);
    }

    public function test_tmdb_film_search_is_normalised()
    {
        Http::fake(['api.themoviedb.org/3/search/movie*' => Http::response(['results' => [
            ['id' => 603, 'title' => 'The Matrix', 'release_date' => '1999-03-30', 'poster_path' => '/matrix.jpg'],
            ['id' => 604, 'title' => 'No Poster', 'release_date' => '', 'poster_path' => null],
        ]])]);

        $results = app(MediaSearch::class)->search(MediaType::Film, 'matrix');

        $this->assertCount(2, $results);
        $this->assertSame('603', $results[0]->externalId);
        $this->assertSame('The Matrix', $results[0]->title);
        $this->assertSame(1999, $results[0]->year);
        $this->assertSame('https://image.tmdb.org/t/p/w342/matrix.jpg', $results[0]->coverUrl);
        $this->assertNull($results[1]->year);
        $this->assertNull($results[1]->coverUrl);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer tmdb-token')
            && $request['query'] === 'matrix');
    }

    public function test_tmdb_tv_search_uses_tv_fields()
    {
        Http::fake(['api.themoviedb.org/3/search/tv*' => Http::response(['results' => [
            ['id' => 1396, 'name' => 'Breaking Bad', 'first_air_date' => '2008-01-20', 'poster_path' => '/bb.jpg'],
        ]])]);

        $result = app(MediaSearch::class)->search(MediaType::Tv, 'breaking')->first();

        $this->assertSame(MediaType::Tv, $result->type);
        $this->assertSame('Breaking Bad', $result->title);
        $this->assertSame(2008, $result->year);
    }

    public function test_rawg_search_is_normalised()
    {
        Http::fake(['api.rawg.io/api/games*' => Http::response(['results' => [
            ['id' => 3328, 'name' => 'The Witcher 3', 'released' => '2015-05-18', 'background_image' => 'https://media.rawg.io/w3.jpg'],
        ]])]);

        $result = app(MediaSearch::class)->search(MediaType::Game, 'witcher')->first();

        $this->assertSame('rawg', $result->source);
        $this->assertSame('3328', $result->externalId);
        $this->assertSame(2015, $result->year);
        $this->assertSame('https://media.rawg.io/w3.jpg', $result->coverUrl);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'key=rawg-key') && str_contains($request->url(), 'search=witcher'));
    }

    public function test_open_library_search_is_normalised()
    {
        Http::fake(['openlibrary.org/search.json*' => Http::response(['docs' => [
            ['key' => '/works/OL27448W', 'title' => 'The Lord of the Rings', 'first_publish_year' => 1954, 'author_name' => ['J.R.R. Tolkien'], 'cover_i' => 14625765],
        ]])]);

        $result = app(MediaSearch::class)->search(MediaType::Book, 'lord of the rings')->first();

        $this->assertSame('OL27448W', $result->externalId);
        $this->assertSame('J.R.R. Tolkien', $result->creator);
        $this->assertSame('https://covers.openlibrary.org/b/id/14625765-M.jpg', $result->coverUrl);
    }

    public function test_open_library_find_looks_up_the_work_by_key()
    {
        Http::fake(['openlibrary.org/search.json*' => Http::response(['docs' => [
            ['key' => '/works/OL27448W', 'title' => 'The Lord of the Rings', 'first_publish_year' => 1954, 'author_name' => ['J.R.R. Tolkien'], 'cover_i' => 14625765],
        ]])]);

        $result = app(MediaSearch::class)->find(MediaType::Book, 'OL27448W');

        $this->assertSame('OL27448W', $result->externalId);
        $this->assertSame(1954, $result->year);
        Http::assertSent(fn ($request) => $request['q'] === 'key:/works/OL27448W');
    }

    public function test_open_library_find_rejects_malformed_ids()
    {
        Http::fake();

        $this->assertNull(app(MediaSearch::class)->find(MediaType::Book, '../authors/OL1A'));
        Http::assertNothingSent();
    }

    public function test_find_returns_null_when_not_found()
    {
        Http::fake(['api.themoviedb.org/*' => Http::response([], 404)]);

        $this->assertNull(app(MediaSearch::class)->find(MediaType::Film, '999999999'));
    }

    public function test_api_failures_become_provider_exceptions()
    {
        Http::fake(['api.rawg.io/*' => Http::response([], 500)]);

        $this->expectException(MediaProviderException::class);

        app(MediaSearch::class)->search(MediaType::Game, 'anything');
    }

    public function test_missing_api_key_is_reported()
    {
        config(['services.tmdb.token' => null]);

        $this->expectException(MediaProviderException::class);
        $this->expectExceptionMessage('TMDB_TOKEN');

        app(MediaSearch::class)->search(MediaType::Film, 'matrix');
    }
}
