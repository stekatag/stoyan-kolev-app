<?php

use App\Models\Category;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('public catalog payload prefers bucket urls when bucket delivery is configured', function () {
    Storage::fake('public');

    config([
        'filesystems.disks.s3.url' => 'https://cdn.example.com',
    ]);

    Video::query()->delete();
    Category::query()->delete();

    Storage::disk('public')->put('assets/intro/Intro.mp4', 'intro-video');
    Storage::disk('public')->put('assets/profile/profile.jpg', 'profile-image');
    Storage::disk('public')->put('assets/homepage/initial screen.png', 'homepage-image');
    Storage::disk('public')->put('assets/thumbnails/qdosan/1.jpeg', 'thumbnail-image');
    Storage::disk('public')->put('assets/videos/qdosan/only-clip.mp4', 'video-content');

    $category = Category::factory()->create([
        'name' => 'Qdosan',
        'slug' => 'qdosan',
        'homepage_hotspot_key' => 'qdosan',
        'is_visible' => true,
        'modal_preview_image_path' => 'thumbnails/qdosan/1.jpeg',
    ]);

    Video::factory()->create([
        'category_id' => $category->id,
        'title' => 'Only clip',
        'slug' => 'only-clip',
        'sort_order' => 1,
        'video_path' => 'videos/qdosan/only-clip.mp4',
        'thumbnail_path' => 'thumbnails/qdosan/1.jpeg',
    ]);

    $video = Video::query()->latest('id')->firstOrFail();

    $this->get(route('home'))
        ->assertInertia(fn(Assert $page) => $page
            ->where('introVideoUrl', 'https://cdn.example.com/assets/intro/Intro.mp4')
            ->where('profileImageUrl', 'https://cdn.example.com/assets/profile/profile.jpg')
            ->where('homepageImageUrl', 'https://cdn.example.com/assets/homepage/initial%20screen.png')
            ->where('categoriesByHotspot.qdosan.previewImageUrl', 'https://cdn.example.com/assets/thumbnails/qdosan/1.jpeg')
            ->where('categoriesByHotspot.qdosan.directVideo.videoUrl', 'https://cdn.example.com/assets/videos/qdosan/only-clip.mp4')
            ->where('categoriesByHotspot.qdosan.directVideo.thumbnailUrl', 'https://cdn.example.com/assets/thumbnails/qdosan/1.jpeg'));
});
