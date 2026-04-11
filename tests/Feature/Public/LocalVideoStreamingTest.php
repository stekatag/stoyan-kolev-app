<?php

use App\Enums\AssetStorageStatus;
use App\Models\Category;
use App\Models\Video;

test('local video streaming endpoint supports byte range requests', function () {
    $category = Category::factory()->create([
        'is_visible' => true,
    ]);

    $video = Video::factory()->create([
        'category_id' => $category->id,
        'local_video_path' => 'assets/videos/Intro.mp4',
        'video_storage_status' => AssetStorageStatus::LocalOnly,
        'bucket_video_key' => null,
    ]);

    $this->withHeaders([
        'Range' => 'bytes=0-1023',
    ])->get(route('videos.stream', $video))
        ->assertStatus(206)
        ->assertHeader('Accept-Ranges', 'bytes');
});
