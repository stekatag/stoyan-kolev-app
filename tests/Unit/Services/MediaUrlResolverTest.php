<?php

use App\Enums\AssetStorageStatus;
use App\Models\Video;
use App\Services\Media\MediaUrlResolver;

test('media url resolver prefers bucket urls when the asset is bucket-backed', function () {
    config([
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);

    $video = Video::factory()->make([
        'category_id' => null,
        'bucket_video_key' => 'videos/example.mp4',
        'local_video_path' => 'assets/videos/example.mp4',
        'video_storage_status' => AssetStorageStatus::BucketAndLocal,
    ]);

    $url = app(MediaUrlResolver::class)->resolveVideoUrl($video);

    expect($url)->toBe('https://cdn.example.com/videos/example.mp4');
});

test('media url resolver falls back to local urls for local-only assets', function () {
    $video = Video::factory()->make([
        'category_id' => null,
        'id' => 42,
        'local_video_path' => 'videos/example.mp4',
        'video_storage_status' => AssetStorageStatus::LocalOnly,
    ]);

    $url = app(MediaUrlResolver::class)->resolveVideoUrl($video);

    expect($url)->toBe(route('videos.stream', ['video' => 42], false));
});
