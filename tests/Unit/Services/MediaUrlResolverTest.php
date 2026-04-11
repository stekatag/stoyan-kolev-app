<?php

use App\Models\Video;
use App\Services\Media\MediaUrlResolver;
use Illuminate\Support\Facades\Storage;

test('media url resolver falls back to local streaming urls when bucket delivery is not configured', function () {
    Storage::fake('public');
    Storage::disk('public')->put('assets/videos/example.mp4', 'video');

    config([
        'filesystems.disks.s3.url' => null,
        'filesystems.disks.s3.endpoint' => null,
        'filesystems.disks.s3.bucket' => null,
    ]);

    $video = Video::factory()->make([
        'category_id' => null,
        'id' => 42,
        'video_path' => 'videos/example.mp4',
    ]);

    $url = app(MediaUrlResolver::class)->resolveVideoUrl($video);

    expect($url)->toBe(route('videos.stream', ['video' => 42], false));
});

test('media url resolver prefers bucket urls when bucket delivery is configured', function () {
    Storage::fake('public');
    Storage::disk('public')->put('assets/videos/example.mp4', 'video');

    config([
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);

    $video = Video::factory()->make([
        'category_id' => null,
        'video_path' => 'videos/example.mp4',
    ]);

    $url = app(MediaUrlResolver::class)->resolveVideoUrl($video);

    expect($url)->toBe('https://cdn.example.com/assets/videos/example.mp4');
});
