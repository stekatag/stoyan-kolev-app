<?php

use App\Models\Category;
use App\Models\Video;
use App\Services\Media\AssetCatalogImportService;
use Illuminate\Support\Facades\Storage;

test('asset catalog import service is idempotent for imported videos', function () {
    Storage::fake('public');
    Storage::fake('s3');

    Video::query()->delete();
    Category::query()->delete();

    $service = app(AssetCatalogImportService::class);

    $firstRun = $service->import();
    $secondRun = $service->import();

    $qdosanCategory = Category::query()->where('slug', 'qdosan')->firstOrFail();
    $qdosanVideos = Video::query()
        ->whereBelongsTo($qdosanCategory)
        ->orderBy('sort_order')
        ->get();

    expect($firstRun->videosCreated)->toBeGreaterThan(0)
        ->and($secondRun->videosCreated)->toBe(0)
        ->and($secondRun->videosUpdated)->toBeGreaterThan(0)
        ->and(Video::query()->count())->toBeGreaterThan(0)
        ->and(Storage::disk('public')->exists('assets/intro/Intro.mp4'))->toBeTrue()
        ->and(Storage::disk('public')->exists('assets/profile/profile.jpg'))->toBeTrue()
        ->and($qdosanCategory->modal_preview_image_path)->toBe('thumbnails/qdosan/1.jpeg')
        ->and($qdosanVideos->pluck('thumbnail_path')->all())->toBe([
            'thumbnails/qdosan/1.jpeg',
            'thumbnails/qdosan/2.jpeg',
            'thumbnails/qdosan/3.jpeg',
        ])
        ->and(Storage::disk('s3')->allFiles())->toBe([]);
});
