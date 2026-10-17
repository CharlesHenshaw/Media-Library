<?php

namespace App\Http\Requests;

use App\Enums\EntryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLibraryEntryRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::entryRules();
    }

    /**
     * Rules shared by creating and updating an entry.
     *
     * @return array<string, array<mixed>>
     */
    public static function entryRules(): array
    {
        return [
            'status' => ['required', Rule::enum(EntryStatus::class)],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'finished_on' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }
}
