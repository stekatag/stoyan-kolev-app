<?php

use App\Models\Category;
use App\Models\Video;
use App\Services\Media\AssetCatalogImportService;
use Illuminate\Support\Facades\Storage;

test('asset catalog import service is idempotent for imported videos', function () {
    Storage::fake('public');
    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    Video::query()->delete();
    Category::query()->delete();

    $service = app(AssetCatalogImportService::class);

    $firstRun = $service->import();
    $secondRun = $service->import();

    expect($firstRun->videosCreated)->toBeGreaterThan(0)
        ->and($secondRun->videosCreated)->toBe(0)
        ->and($secondRun->videosUpdated)->toBeGreaterThan(0)
        ->and(Video::query()->count())->toBeGreaterThan(0)
        ->and(Storage::disk('public')->exists('assets/videos/Intro.mp4'))->toBeTrue()
        ->and(Storage::disk('s3')->allFiles())->toBe([]);
});
