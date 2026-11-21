<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    public const TYPE_ANIME = 'anime';
    public const TYPE_MANGA = 'manga';
    public const TYPES = [self::TYPE_ANIME, self::TYPE_MANGA];

    // Nama tabel di-set eksplisit: "media" sudah bentuk jamak sekaligus tunggal.
    protected $table = 'media';

    protected $fillable = [
        'title',
        'type',
        'genre',
        'release_year',
        'episodes',
        'chapters',
    ];

    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
            'episodes' => 'integer',
            'chapters' => 'integer',
        ];
    }

    /**
     * Pengguna yang memasukkan media ini ke koleksinya (many-to-many).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'media_user')->withTimestamps();
    }

    /**
     * Padanan getDetails() dari Modul 3:
     * "Jujutsu Kaisen | Action | 2020 | 24 episodes"
     */
    protected function details(): Attribute
    {
        return Attribute::get(function () {
            if ($this->type === self::TYPE_ANIME) {
                $label = $this->episodes === 1 ? 'episode' : 'episodes';
                $extra = "{$this->episodes} {$label}";
            } else {
                $label = $this->chapters === 1 ? 'chapter' : 'chapters';
                $extra = "{$this->chapters} {$label}";
            }

            return "{$this->title} | {$this->genre} | {$this->release_year} | {$extra}";
        });
    }

    /**
     * Padanan searchByTitle() dari Modul 3. Pemakaian: Media::searchTitle('jujutsu')
     */
    public function scopeSearchTitle(Builder $query, ?string $keyword): Builder
    {
        $keyword = trim((string) $keyword);

        return $keyword === ''
            ? $query
            : $query->where('title', 'like', '%'.addcslashes($keyword, '%_\\').'%');
    }
}
