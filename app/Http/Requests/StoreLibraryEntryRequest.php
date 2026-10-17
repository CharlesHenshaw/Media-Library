<?php

namespace App\Http\Requests;

use App\Enums\MediaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLibraryEntryRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(MediaType::class)],
            'external_id' => ['required', 'string', 'max:64'],
            ...UpdateLibraryEntryRequest::entryRules(),
        ];
    }
}
