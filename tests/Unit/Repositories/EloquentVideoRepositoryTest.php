<?php

use App\Models\Category;
use App\Models\Video;
use App\Repositories\EloquentVideoRepository;

test('video repository returns visible category videos in sort order', function () {
    Video::query()->delete();
    Category::query()->delete();

    $category = Category::factory()->create();

    Video::factory()->create([
        'category_id' => $category->getKey(),
        'title' => 'Second',
        'slug' => 'second',
        'sort_order' => 2,
        'is_visible' => true,
    ]);

    Video::factory()->create([
        'category_id' => $category->getKey(),
        'title' => 'Hidden',
        'slug' => 'hidden',
        'sort_order' => 0,
        'is_visible' => false,
    ]);

    Video::factory()->create([
        'category_id' => $category->getKey(),
        'title' => 'First',
        'slug' => 'first',
        'sort_order' => 1,
        'is_visible' => true,
    ]);

    $videos = app(EloquentVideoRepository::class)->getVisibleByCategoryId($category->getKey());

    expect($videos->pluck('title')->all())->toBe(['First', 'Second']);
});
