<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MediaItem */
class MediaItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'year' => $this->year,
            'cover_url' => $this->cover_url,
            'creator' => $this->creator,
        ];
    }
}
