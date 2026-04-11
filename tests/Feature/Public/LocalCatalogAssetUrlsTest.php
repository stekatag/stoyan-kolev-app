<?php

use App\Enums\AssetStorageStatus;
use App\Models\Category;
use App\Models\Video;
use Inertia\Testing\AssertableInertia as Assert;

test('public catalog payload uses storage urls for local fallback assets', function () {
    Video::query()->delete();
    Category::query()->delete();

    $category = Category::factory()->create([
        'name' => 'Qdosan',
        'slug' => 'qdosan',
        'homepage_hotspot_key' => 'qdosan',
        'is_visible' => true,
        'modal_preview_image_path' => 'assets/screenshots/qdosan.png',
    ]);

    Video::factory()->create([
        'category_id' => $category->id,
        'title' => 'Only clip',
        'slug' => 'only-clip',
        'sort_order' => 1,
        'local_video_path' => 'assets/videos/qdosan/only-clip.mp4',
        'local_thumbnail_path' => 'assets/screenshots/qdosan.png',
        'video_storage_status' => AssetStorageStatus::LocalOnly,
        'thumbnail_storage_status' => AssetStorageStatus::LocalOnly,
    ]);

    $video = Video::query()->latest('id')->firstOrFail();

    $this->get(route('home'))
        ->assertInertia(fn(Assert $page) => $page
            ->where('introVideoUrl', '/storage/assets/videos/Intro.mp4')
            ->where('profileImageUrl', '/storage/assets/screenshots/qdosan.png')
            ->where('homepageImageUrl', '/storage/assets/initial%20screen/initial%20screen.png')
            ->where('categoriesByHotspot.qdosan.previewImageUrl', '/storage/assets/screenshots/qdosan.png')
            ->where('categoriesByHotspot.qdosan.directVideo.videoUrl', route('videos.stream', $video, false))
            ->where('categoriesByHotspot.qdosan.directVideo.thumbnailUrl', '/storage/assets/screenshots/qdosan.png'));
});
