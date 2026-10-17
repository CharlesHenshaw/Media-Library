<?php

namespace App\Models;

use App\Enums\EntryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryEntry extends Model
{
    /** @use HasFactory<\Database\Factories\LibraryEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'media_item_id',
        'status',
        'rating',
        'finished_on',
    ];

    protected function casts(): array
    {
        return [
            'status' => EntryStatus::class,
            'rating' => 'integer',
            'finished_on' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mediaItem(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class);
    }
}
