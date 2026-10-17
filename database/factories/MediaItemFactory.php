<?php

namespace Database\Factories;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaItem>
 */
class MediaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => MediaType::Film,
            'source' => 'tmdb',
            'external_id' => (string) fake()->unique()->numberBetween(1, 999999),
            'title' => fake()->sentence(3),
            'year' => fake()->year(),
            'cover_url' => fake()->imageUrl(),
            'creator' => fake()->name(),
        ];
    }

    public function ofType(MediaType $type): static
    {
        return $this->state(fn () => [
            'type' => $type,
            'source' => match ($type) {
                MediaType::Film, MediaType::Tv => 'tmdb',
                MediaType::Game => 'rawg',
                MediaType::Book => 'openlibrary',
            },
        ]);
    }
}
