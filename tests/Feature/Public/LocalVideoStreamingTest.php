<?php

use App\Models\Category;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;

test('local video streaming endpoint supports byte range requests', function () {
    Storage::disk('public')->put('assets/videos/Intro.mp4', 'test-video-content');

    $category = Category::factory()->create([
        'is_visible' => true,
    ]);

    $video = Video::factory()->create([
        'category_id' => $category->id,
        'video_path' => 'videos/Intro.mp4',
    ]);

    $this->withHeaders([
        'Range' => 'bytes=0-1023',
    ])->get(route('videos.stream', $video))
        ->assertStatus(206)
        ->assertHeader('Accept-Ranges', 'bytes');
});
