<?php

use App\Models\Category;
use App\Models\Video;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

test('the asset sync command uploads packaged assets without importing catalog records', function () {
    Storage::fake('public');
    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    config()->set('filesystems.disks.s3.key', 'test-key');
    config()->set('filesystems.disks.s3.secret', 'test-secret');
    config()->set('filesystems.disks.s3.region', 'auto');
    config()->set('filesystems.disks.s3.bucket', 'test-bucket');
    config()->set('filesystems.disks.s3.endpoint', 'https://example-r2.invalid');
    config()->set('stoyan_kolev.homepage_image.bucket_key', 'stoyan/homepage/initial screen.png');

    Storage::disk('public')->put('assets/initial screen/initial screen.png', 'image');
    Storage::disk('public')->put('assets/videos/Intro.mp4', 'video');

    $video = Video::factory()->create([
        'local_video_path' => 'assets/videos/qdosan/only-clip.mp4',
        'bucket_video_key' => 'stoyan/videos/qdosan/only-clip.mp4',
        'local_thumbnail_path' => 'assets/screenshots/qdosan.png',
        'bucket_thumbnail_key' => 'stoyan/screenshots/qdosan.png',
    ]);

    Storage::disk('public')->put($video->local_video_path, 'video-file');
    Storage::disk('public')->put($video->local_thumbnail_path, 'thumbnail-file');

    $existingCategoryCount = Category::query()->count();
    $existingVideoCount = Video::query()->count();

    Artisan::call('stoyan:sync-assets');

    expect(Category::query()->count())->toBe($existingCategoryCount)
        ->and(Video::query()->count())->toBe($existingVideoCount);

    expect(Storage::disk('s3')->exists('stoyan/homepage/initial screen.png'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('stoyan/intro/Intro.mp4'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('stoyan/videos/qdosan/only-clip.mp4'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('stoyan/screenshots/qdosan.png'))->toBeFalse()
        ->and(Storage::disk('stoyan_recovery')->exists('stoyan/homepage/initial screen.png'))->toBeTrue();
});

test('the asset sync command fails fast when the bucket is not configured', function () {
    Storage::fake('public');
    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    config()->set('filesystems.disks.s3.key', null);
    config()->set('filesystems.disks.s3.secret', null);
    config()->set('filesystems.disks.s3.region', null);
    config()->set('filesystems.disks.s3.bucket', null);
    config()->set('filesystems.disks.s3.endpoint', null);

    $this->artisan('stoyan:sync-assets')
        ->expectsOutputToContain('S3 bucket storage is not fully configured. Missing: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET, AWS_ENDPOINT, AWS_DEFAULT_REGION.')
        ->assertFailed();
});
