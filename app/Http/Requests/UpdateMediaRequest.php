<?php

namespace App\Http\Requests;

use App\Models\Media;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMediaRequest extends BaseMediaRequest
{
    public function rules(): array
    {
        /** @var Media $media */
        $media = $this->route('media');

        return [
            // Judul tetap harus unik, tapi boleh sama dengan judul milik media ini sendiri.
            'title' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('media', 'title')->ignore($media)],
            'type' => ['prohibited'],
            'genre' => ['sometimes', 'required', 'string', 'max:100'],
            'release_year' => ['sometimes', 'required', 'integer', 'between:1900,'.$this->maxYear()],
            'episodes' => ['sometimes', 'integer', 'min:1'],
            'chapters' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * Episode hanya untuk anime, chapter hanya untuk manga.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var Media $media */
                $media = $this->route('media');

                if ($media->type === Media::TYPE_ANIME && $this->has('chapters')) {
                    $validator->errors()->add('chapters', $this->messages()['chapters.prohibited_if']);
                }

                if ($media->type === Media::TYPE_MANGA && $this->has('episodes')) {
                    $validator->errors()->add('episodes', $this->messages()['episodes.prohibited_if']);
                }
            },
        ];
    }
}
