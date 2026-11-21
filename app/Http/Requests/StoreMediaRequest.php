<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Validation\Rule;

class StoreMediaRequest extends BaseMediaRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:media,title'],
            'type' => ['required', Rule::in(Media::TYPES)],
            'genre' => ['required', 'string', 'max:100'],
            'release_year' => ['required', 'integer', 'between:1900,'.$this->maxYear()],

            // Anime wajib punya episodes, Manga wajib punya chapters (seperti Modul 3).
            'episodes' => ['required_if:type,anime', 'prohibited_if:type,manga', 'nullable', 'integer', 'min:1'],
            'chapters' => ['required_if:type,manga', 'prohibited_if:type,anime', 'nullable', 'integer', 'min:1'],
        ];
    }
}
