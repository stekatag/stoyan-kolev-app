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
    $kaishCategory = Category::query()->where('slug', 'kaish')->firstOrFail();
    $kaishVideos = Video::query()
        ->whereBelongsTo($kaishCategory)
        ->orderBy('sort_order')
        ->get();
    $veselCategory = Category::query()->where('slug', 'vesel')->firstOrFail();
    $veselVideos = Video::query()
        ->whereBelongsTo($veselCategory)
        ->orderBy('sort_order')
        ->get();

    expect($firstRun->videosCreated)->toBeGreaterThan(0)
        ->and($secondRun->videosCreated)->toBe(0)
        ->and($secondRun->videosUpdated)->toBeGreaterThan(0)
        ->and(Video::query()->count())->toBeGreaterThan(0)
        ->and(Storage::disk('public')->exists('assets/intro/Intro.mp4'))->toBeTrue()
        ->and(Storage::disk('public')->exists('assets/profile/profile.jpg'))->toBeTrue()
        ->and($qdosanCategory->modal_preview_image_path)->toBe('thumbnails/qdosan/1.jpeg')
        ->and($qdosanVideos->pluck('original_filename')->all())->toBe([
            'STOYAN_KOLEV_E_BESEN_CHE_NQMA_WESTER_UNION.mp4',
            'Stoyan Kolev psuva I otkacha s machete.mp4',
            'Стоян Колев тряска ребро.mp4',
        ])
        ->and($qdosanVideos->pluck('thumbnail_path')->all())->toBe([
            'thumbnails/qdosan/1.jpeg',
            'thumbnails/qdosan/2.jpeg',
            'thumbnails/qdosan/3.jpeg',
        ])
        ->and($kaishVideos->pluck('original_filename')->all())->toBe([
            'Stoyan Kolev Visualised.mp4',
            'stoyan kolev is my fighter.mp4',
            'СТОЯН КОЛЕВ ОВЛАДЯ ТЪМНАТА ЕНЕРГИЯ.mp4',
            'Стоян Колев ЧУПИ И ЯДЕ ТЕЛЕФОНА 19.mp4',
        ])
        ->and($kaishVideos->pluck('thumbnail_path')->all())->toBe([
            'thumbnails/kaish/1.jpeg',
            'thumbnails/kaish/2.png',
            'thumbnails/kaish/3.png',
            'thumbnails/kaish/4.jpeg',
        ])
        ->and($veselVideos->pluck('original_filename')->all())->toBe([
            'Stoyan Kolev reketira stopadjii.mp4',
            'Стоян Колев вилнее във фитнеса.mp4',
            'Стоян Колев на лостове.mp4',
            'Стоян Колев с брадвата прай въртележки.mp4',
            'Стоян Колев се ебава с циганин.mp4',
            'дебелия.mp4',
        ])
        ->and($veselVideos->pluck('thumbnail_path')->all())->toBe([
            'thumbnails/vesel/1.jpeg',
            'thumbnails/vesel/2.jpeg',
            'thumbnails/vesel/3.jpeg',
            'thumbnails/vesel/4.png',
            'thumbnails/vesel/5.jpeg',
            'thumbnails/vesel/6.jpeg',
        ])
        ->and(Storage::disk('s3')->allFiles())->toBe([]);
});
