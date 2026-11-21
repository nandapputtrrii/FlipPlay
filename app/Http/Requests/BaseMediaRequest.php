<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Pesan error & nama atribut yang dipakai bersama oleh Store/UpdateMediaRequest.
 */
abstract class BaseMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // belum ada autentikasi di demo ini
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'type' => 'jenis media',
            'genre' => 'genre',
            'release_year' => 'tahun rilis',
            'episodes' => 'jumlah episode',
            'chapters' => 'jumlah chapter',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul media wajib diisi.',
            'title.unique' => 'Judul media sudah terdaftar di FlipPlay.',
            'type.required' => 'Jenis media wajib diisi (anime atau manga).',
            'type.in' => 'Jenis media harus berupa anime atau manga.',
            'type.prohibited' => 'Jenis media tidak dapat diubah setelah dibuat.',
            'genre.required' => 'Genre wajib diisi.',
            'release_year.required' => 'Tahun rilis wajib diisi.',
            'release_year.integer' => 'Tahun rilis harus berupa angka bulat.',
            'release_year.between' => 'Tahun rilis harus antara :min dan :max.',
            'episodes.required_if' => 'Jumlah episode wajib diisi untuk anime.',
            'episodes.prohibited_if' => 'Manga tidak memiliki jumlah episode (gunakan chapters).',
            'episodes.integer' => 'Jumlah episode harus berupa angka bulat.',
            'episodes.min' => 'Jumlah episode minimal :min.',
            'chapters.required_if' => 'Jumlah chapter wajib diisi untuk manga.',
            'chapters.prohibited_if' => 'Anime tidak memiliki jumlah chapter (gunakan episodes).',
            'chapters.integer' => 'Jumlah chapter harus berupa angka bulat.',
            'chapters.min' => 'Jumlah chapter minimal :min.',
        ];
    }

    protected function maxYear(): int
    {
        return (int) date('Y') + 2;
    }
}
