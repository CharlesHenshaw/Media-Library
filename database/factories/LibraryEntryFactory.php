<?php

namespace Database\Factories;

use App\Enums\EntryStatus;
use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LibraryEntry>
 */
class LibraryEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'media_item_id' => MediaItem::factory(),
            'status' => EntryStatus::Finished,
            'rating' => fake()->numberBetween(1, 5),
            'finished_on' => fake()->date(),
        ];
    }
}
