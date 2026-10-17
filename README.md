# Media Library

Log the films, TV shows, games and books you've finished (or given up on), rate them out of 5, and browse your library by cover.

Built with Laravel 12, Inertia and Vue 3 (the official Laravel Vue starter kit).

## Data sources

| Media    | API                                                   | Key needed |
| -------- | ----------------------------------------------------- | ---------- |
| Films/TV | [TMDB](https://developer.themoviedb.org/docs)         | Yes — free account → Settings → API → **API Read Access Token** |
| Games    | [RAWG](https://rawg.io/apidocs)                       | Yes — free key |
| Books    | [Open Library](https://openlibrary.org/developers/api) | No |

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Add your keys to `.env`:

```
TMDB_TOKEN=your-tmdb-read-access-token
RAWG_KEY=your-rawg-key
```

Then run the app (server, queue, logs and Vite together):

```bash
composer run dev
```

Visit http://localhost:8000, register, and head to **Search**.

## Tests

```bash
php artisan test
```

External APIs are faked with `Http::fake()`, so tests don't need keys or network access.
