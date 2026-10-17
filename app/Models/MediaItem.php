<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaItem extends Model
{
    /** @use HasFactory<\Database\Factories\MediaItemFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'source',
        'external_id',
        'title',
        'year',
        'cover_url',
        'creator',
    ];

    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'year' => 'integer',
        ];
    }

    public function libraryEntries(): HasMany
    {
        return $this->hasMany(LibraryEntry::class);
    }
}
