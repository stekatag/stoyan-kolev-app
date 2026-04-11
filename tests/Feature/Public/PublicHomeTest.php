<?php

use App\Enums\AssetStorageStatus;
use App\Models\Category;
use App\Models\Video;
use Inertia\Testing\AssertableInertia as Assert;

test('the public home route is available without authentication', function () {
    Video::query()->delete();
    Category::query()->delete();

    $category = Category::factory()->create([
        'name' => 'Qdosan',
        'slug' => 'qdosan',
        'homepage_hotspot_key' => 'qdosan',
        'is_visible' => true,
        'sort_order' => 1,
    ]);

    Video::factory()->create([
        'category_id' => $category->id,
        'title' => 'Only clip',
        'slug' => 'only-clip',
        'sort_order' => 1,
        'video_storage_status' => AssetStorageStatus::LocalOnly,
        'thumbnail_storage_status' => AssetStorageStatus::Missing,
    ]);

    $response = $this->get(route('home'));

    $response->assertInertia(
        fn(Assert $page) => $page
            ->component('Public/Home')
            ->has('introVideoUrl')
            ->has('profileImageUrl')
            ->has('homepageImageUrl')
            ->has('hotspots', 6)
            ->has('categoriesByHotspot.qdosan')
            ->where('categoriesByHotspot.qdosan.videoCount', 1)
            ->where('categoriesByHotspot.qdosan.opensDirectly', true)
    );
});

test('starter auth and settings routes are not exposed publicly', function () {
    $this->get('/login')->assertNotFound();
    $this->get('/register')->assertNotFound();
    $this->get('/forgot-password')->assertNotFound();
    $this->get('/email/verify')->assertNotFound();
    $this->get('/two-factor-challenge')->assertNotFound();
    $this->get('/user/confirm-password')->assertNotFound();
    $this->get('/settings/profile')->assertNotFound();
});

test('the filament login entrypoint remains available to guests', function () {
    $this->get('/admin/login')->assertOk();
});
