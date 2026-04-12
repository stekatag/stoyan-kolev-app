<?php

use App\Models\Category;
use App\Models\Video;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

test('the asset sync command uploads packaged assets without importing catalog records', function () {
    Storage::fake('public');
    Storage::fake('s3');

    config()->set('filesystems.disks.s3.key', 'test-key');
    config()->set('filesystems.disks.s3.secret', 'test-secret');
    config()->set('filesystems.disks.s3.region', 'auto');
    config()->set('filesystems.disks.s3.bucket', 'test-bucket');
    config()->set('filesystems.disks.s3.endpoint', 'https://example-r2.invalid');
    config()->set('stoyan_kolev.homepage_image.canonical_path', 'homepage/initial screen.png');
    config()->set('stoyan_kolev.profile_image.canonical_path', 'profile/profile.jpg');

    Storage::disk('public')->put('assets/homepage/initial screen.png', 'image');
    Storage::disk('public')->put('assets/intro/Intro.mp4', 'video');
    Storage::disk('public')->put('assets/profile/profile.jpg', 'profile');

    $video = Video::factory()->create([
        'video_path' => 'videos/qdosan/only-clip.mp4',
        'thumbnail_path' => 'thumbnails/qdosan/1.jpeg',
    ]);

    Storage::disk('public')->put('assets/' . $video->video_path, 'video-file');
    Storage::disk('public')->put('assets/' . $video->thumbnail_path, 'thumbnail-file');

    $existingCategoryCount = Category::query()->count();
    $existingVideoCount = Video::query()->count();

    Artisan::call('stoyan:sync-assets');

    expect(Category::query()->count())->toBe($existingCategoryCount)
        ->and(Video::query()->count())->toBe($existingVideoCount);

    expect(Storage::disk('s3')->exists('assets/homepage/initial screen.png'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('assets/intro/Intro.mp4'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('assets/profile/profile.jpg'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('assets/videos/qdosan/only-clip.mp4'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('assets/thumbnails/qdosan/1.jpeg'))->toBeTrue();
});

test('the asset sync command fails fast when the bucket is not configured', function () {
    Storage::fake('public');
    Storage::fake('s3');

    config()->set('filesystems.disks.s3.key', null);
    config()->set('filesystems.disks.s3.secret', null);
    config()->set('filesystems.disks.s3.region', null);
    config()->set('filesystems.disks.s3.bucket', null);
    config()->set('filesystems.disks.s3.endpoint', null);

    $this->artisan('stoyan:sync-assets')
        ->expectsOutputToContain('Bucket disk [s3] is not fully configured. Missing: key, secret, bucket, endpoint, region.')
        ->assertFailed();
});

test('the asset sync command uses the default filesystem disk when it is a cloud disk', function () {
    Storage::fake('public');
    Storage::fake('r2');

    config()->set('filesystems.default', 'r2');
    config()->set('filesystems.disks.s3.key', null);
    config()->set('filesystems.disks.s3.secret', null);
    config()->set('filesystems.disks.s3.region', null);
    config()->set('filesystems.disks.s3.bucket', null);
    config()->set('filesystems.disks.s3.endpoint', null);
    config()->set('filesystems.disks.r2.key', 'cloud-key');
    config()->set('filesystems.disks.r2.secret', 'cloud-secret');
    config()->set('filesystems.disks.r2.region', 'auto');
    config()->set('filesystems.disks.r2.bucket', 'cloud-bucket');
    config()->set('filesystems.disks.r2.endpoint', 'https://cloudflare-r2.example');
    config()->set('stoyan_kolev.homepage_image.canonical_path', 'homepage/initial screen.png');

    Storage::disk('public')->put('assets/homepage/initial screen.png', 'image');

    $this->artisan('stoyan:sync-assets')->assertSuccessful();

    expect(Storage::disk('r2')->exists('assets/homepage/initial screen.png'))->toBeTrue();
});

test('the asset sync command still uses s3 when the default filesystem disk is local', function () {
    Storage::fake('public');
    Storage::fake('s3');

    config()->set('filesystems.default', 'local');
    config()->set('filesystems.disks.s3.key', 'test-key');
    config()->set('filesystems.disks.s3.secret', 'test-secret');
    config()->set('filesystems.disks.s3.region', 'auto');
    config()->set('filesystems.disks.s3.bucket', 'test-bucket');
    config()->set('filesystems.disks.s3.endpoint', 'https://example-r2.invalid');
    config()->set('stoyan_kolev.homepage_image.canonical_path', 'homepage/initial screen.png');

    Storage::disk('public')->put('assets/homepage/initial screen.png', 'image');

    $this->artisan('stoyan:sync-assets')->assertSuccessful();

    expect(Storage::disk('s3')->exists('assets/homepage/initial screen.png'))->toBeTrue();
});
