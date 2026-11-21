<?php

namespace Tests\Unit;

use App\Models\Media;
use Tests\TestCase;

class MediaDetailsTest extends TestCase
{
    public function test_details_matches_modul3_format_for_anime(): void
    {
        $media = new Media(['title' => 'Jujutsu Kaisen', 'type' => 'anime', 'genre' => 'Action', 'release_year' => 2020, 'episodes' => 24]);

        $this->assertSame('Jujutsu Kaisen | Action | 2020 | 24 episodes', $media->details);
    }

    public function test_details_uses_singular_label_for_single_chapter(): void
    {
        $media = new Media(['title' => 'Oneshot', 'type' => 'manga', 'genre' => 'Drama', 'release_year' => 2021, 'chapters' => 1]);

        $this->assertSame('Oneshot | Drama | 2021 | 1 chapter', $media->details);
    }
}
