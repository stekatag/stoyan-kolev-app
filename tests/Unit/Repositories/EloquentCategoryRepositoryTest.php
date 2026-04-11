<?php

use App\Models\Category;
use App\Models\Video;
use App\Repositories\EloquentCategoryRepository;

test('category repository returns only visible homepage categories with hotspot keys', function () {
    Video::query()->delete();
    Category::query()->delete();

    Category::factory()->create([
        'homepage_hotspot_key' => 'qdosan',
        'is_visible' => true,
        'sort_order' => 2,
    ]);

    Category::factory()->create([
        'homepage_hotspot_key' => null,
        'is_visible' => true,
    ]);

    Category::factory()->create([
        'homepage_hotspot_key' => 'kaish',
        'is_visible' => false,
    ]);

    $categories = app(EloquentCategoryRepository::class)->getVisibleHomepageCategories();

    expect($categories)->toHaveCount(1)
        ->and($categories->first()->homepage_hotspot_key)->toBe('qdosan');
});
