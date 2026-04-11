<?php

use App\Enums\VideoSourceType;
use App\Models\Category;
use App\Models\Video;
use App\Services\Media\AdminMediaUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('admin media upload service stores canonical media paths and uploads bucket copies', function () {
    Video::query()->delete();
    Category::query()->delete();

    Storage::fake('public');
    Storage::fake('s3');

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
        ->and($video->category_id)->toBe($category->getKey())
        ->and($video->video_path)->toStartWith('videos/uploads/')
        ->and($video->thumbnail_path)->toStartWith('thumbnails/uploads/');

    expect(Storage::disk('public')->exists('assets/' . $video->video_path))->toBeTrue()
        ->and(Storage::disk('public')->exists('assets/' . $video->thumbnail_path))->toBeTrue()
        ->and(Storage::disk('s3')->exists('assets/' . $video->video_path))->toBeTrue()
        ->and(Storage::disk('s3')->exists('assets/' . $video->thumbnail_path))->toBeTrue();
});
