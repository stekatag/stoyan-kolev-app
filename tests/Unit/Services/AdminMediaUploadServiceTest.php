<?php

use App\Enums\VideoSourceType;
use App\Models\Category;
use App\Models\Video;
use App\Services\Media\AdminMediaUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin media upload service mirrors uploaded video assets to both disks', function () {
    Video::query()->delete();
    Category::query()->delete();

    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    $category = Category::factory()->create();
    $videoUpload = UploadedFile::fake()->create('clip.mp4', 2048, 'video/mp4');
    $thumbnailUpload = UploadedFile::fake()->image('clip.jpg');

    $video = app(AdminMediaUploadService::class)->storeVideoUpload(
        $videoUpload,
        categoryId: $category->getKey(),
        title: 'Clip Upload',
        thumbnailUpload: $thumbnailUpload,
        isVisible: true,
        sortOrder: 3,
    );

    expect($video->source_type)->toBe(VideoSourceType::AdminUpload)
        ->and($video->category_id)->toBe($category->getKey());

    expect(Storage::disk('s3')->exists($video->bucket_video_key))->toBeTrue()
        ->and(Storage::disk('stoyan_recovery')->exists($video->bucket_video_key))->toBeTrue()
        ->and(Storage::disk('s3')->exists($video->bucket_thumbnail_key))->toBeTrue();
});
