<?php

use App\Enums\AssetStorageStatus;
use App\Models\Category;
use App\Models\Video;
use Inertia\Testing\AssertableInertia as Assert;

test('multi-video categories open through the category modal payload', function () {
    Video::query()->delete();
    Category::query()->delete();

    $category = Category::factory()->create([
        'name' => 'Kaish',
        'slug' => 'kaish',
        'homepage_hotspot_key' => 'kaish',
        'is_visible' => true,
    ]);

    Video::factory()->count(2)->sequence(
        [
            'category_id' => $category->getKey(),
            'title' => 'Clip One',
            'slug' => 'clip-one',
            'sort_order' => 1,
            'video_storage_status' => AssetStorageStatus::LocalOnly,
        ],
        [
            'category_id' => $category->getKey(),
            'title' => 'Clip Two',
            'slug' => 'clip-two',
            'sort_order' => 2,
            'video_storage_status' => AssetStorageStatus::LocalOnly,
        ],
    )->create([
        'thumbnail_storage_status' => AssetStorageStatus::Missing,
    ]);

    $this->get(route('home'))
        ->assertInertia(fn(Assert $page) => $page
            ->component('Public/Home')
            ->where('categoriesByHotspot.kaish.opensDirectly', false)
            ->where('categoriesByHotspot.kaish.videoCount', 2)
            ->has('categoriesByHotspot.kaish.videos', 2)
            ->where('categoriesByHotspot.kaish.directVideo.title', 'Clip One'));
});
