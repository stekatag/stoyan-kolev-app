<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Video::query()->delete();
    Category::query()->delete();
});

test('non-admin users cannot access the filament panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('admin users can access the filament panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk();
});

test('admins can create categories in filament', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Qdosan',
            'slug' => 'qdosan',
            'homepage_hotspot_key' => 'qdosan',
            'is_visible' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->where('slug', 'qdosan')->exists())->toBeTrue();
});

test('admins cannot reuse an occupied hotspot key', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Category::factory()->create([
        'name' => 'Qdosan',
        'slug' => 'qdosan',
        'homepage_hotspot_key' => 'qdosan',
        'is_visible' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(CreateCategory::class)
        ->fillForm([
            'name' => 'Kaish 2',
            'slug' => 'kaish-2',
            'homepage_hotspot_key' => 'qdosan',
            'is_visible' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['homepage_hotspot_key']);
});

test('admins can edit categories and manage the optional preview image', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $category = Category::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditCategory::class, ['record' => $category->getKey()])
        ->fillForm([
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'is_visible' => false,
            'sort_order' => 10,
            'homepage_hotspot_key' => null,
            'modal_preview_image_path' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh())
        ->name->toBe('Updated Category')
        ->slug->toBe('updated-category')
        ->is_visible->toBeFalse()
        ->sort_order->toBe(10)
        ->homepage_hotspot_key->toBeNull();
});

test('admins can create videos without thumbnails in filament', function () {
    Storage::fake('s3');
    Storage::fake('stoyan_recovery');

    $admin = User::factory()->create(['is_admin' => true]);
    $category = Category::factory()->create();
    $video = UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4');

    $this->actingAs($admin);

    Livewire::test(CreateVideo::class)
        ->fillForm([
            'category_id' => $category->getKey(),
            'title' => 'Clip',
            'video' => $video,
            'is_visible' => true,
            'sort_order' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
