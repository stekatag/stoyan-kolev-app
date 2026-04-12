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

test('media url resolver uses the default filesystem disk when it is a cloud disk', function () {
    Storage::fake('public');
    Storage::disk('public')->put('assets/videos/example.mp4', 'video');

    config([
        'filesystems.default' => 'r2',
        'filesystems.disks.s3.url' => null,
        'filesystems.disks.s3.endpoint' => null,
        'filesystems.disks.s3.bucket' => null,
        'filesystems.disks.r2.url' => 'https://bucket.example.com',
    ]);

    $video = Video::factory()->make([
        'category_id' => null,
        'video_path' => 'videos/example.mp4',
    ]);

    $url = app(MediaUrlResolver::class)->resolveVideoUrl($video);

    expect($url)->toBe('https://bucket.example.com/assets/videos/example.mp4');
});

test('media url resolver still prefers the bucket when the default filesystem disk is local', function () {
    Storage::fake('public');
    Storage::disk('public')->put('assets/videos/example.mp4', 'video');

    config([
        'filesystems.default' => 'local',
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);

    $video = Video::factory()->make([
        'category_id' => null,
        'video_path' => 'videos/example.mp4',
    ]);

    $url = app(MediaUrlResolver::class)->resolveVideoUrl($video);

    expect($url)->toBe('https://cdn.example.com/assets/videos/example.mp4');
});

test('media url resolver percent-encodes bucket asset urls with spaces', function () {
    config([
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);

    $url = app(MediaUrlResolver::class)->resolveAssetUrl('homepage/initial screen.png');

    expect($url)->toBe('https://cdn.example.com/assets/homepage/initial%20screen.png');
});

test('media url resolver percent-encodes bucket video urls with unicode characters', function () {
    config([
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);

    $video = Video::factory()->make([
        'category_id' => null,
        'video_path' => 'videos/vesel/Стоян Колев на лостове.mp4',
    ]);

    $url = app(MediaUrlResolver::class)->resolveVideoUrl($video);

    expect($url)->toBe('https://cdn.example.com/assets/videos/vesel/%D0%A1%D1%82%D0%BE%D1%8F%D0%BD%20%D0%9A%D0%BE%D0%BB%D0%B5%D0%B2%20%D0%BD%D0%B0%20%D0%BB%D0%BE%D1%81%D1%82%D0%BE%D0%B2%D0%B5.mp4');
});
