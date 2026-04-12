<?php

use App\Filament\Pages\ChangePassword;
use App\Filament\Pages\ManageBrandAssets;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Filament\Resources\Videos\Pages\EditVideo;
use App\Filament\Resources\Videos\Pages\ListVideos;
use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use App\Services\Media\MediaUrlResolver;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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

test('admin users can see the change password user menu action', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Change Password');
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
    Storage::fake('public');
    Storage::fake('s3');

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

test('admins can filter videos by category in filament', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $firstCategory = Category::factory()->create(['name' => 'Qdosan']);
    $secondCategory = Category::factory()->create(['name' => 'Kaish']);
    $firstVideo = Video::factory()->create([
        'category_id' => $firstCategory->getKey(),
        'title' => 'First Clip',
    ]);
    $secondVideo = Video::factory()->create([
        'category_id' => $secondCategory->getKey(),
        'title' => 'Second Clip',
    ]);

    $this->actingAs($admin);

    Livewire::test(ListVideos::class)
        ->filterTable('category_id', $firstCategory->getKey())
        ->assertCanSeeTableRecords([$firstVideo])
        ->assertCanNotSeeTableRecords([$secondVideo]);
});

test('admins can edit video metadata and replace uploaded assets in filament', function () {
    Storage::fake('public');
    Storage::fake('s3');

    $admin = User::factory()->create(['is_admin' => true]);
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();
    $video = Video::factory()->create([
        'category_id' => $category->getKey(),
        'title' => 'Old Clip',
        'slug' => 'old-clip',
        'video_path' => 'videos/uploads/old-clip.mp4',
        'thumbnail_path' => 'thumbnails/uploads/old-clip.jpg',
    ]);

    Storage::disk('public')->put('assets/videos/uploads/old-clip.mp4', 'old-video');
    Storage::disk('public')->put('assets/thumbnails/uploads/old-clip.jpg', 'old-thumbnail');
    Storage::disk('s3')->put('assets/videos/uploads/old-clip.mp4', 'old-video');
    Storage::disk('s3')->put('assets/thumbnails/uploads/old-clip.jpg', 'old-thumbnail');

    $this->actingAs($admin);

    Livewire::test(EditVideo::class, ['record' => $video->getKey()])
        ->fillForm([
            'category_id' => $otherCategory->getKey(),
            'title' => 'Updated Clip',
            'video' => UploadedFile::fake()->create('new-clip.mp4', 1024, 'video/mp4'),
            'thumbnail' => UploadedFile::fake()->image('new-clip.jpg'),
            'is_visible' => false,
            'sort_order' => 9,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($video->fresh())
        ->category_id->toBe($otherCategory->getKey())
        ->title->toBe('Updated Clip')
        ->is_visible->toBeFalse()
        ->sort_order->toBe(9)
        ->video_path->not->toBe('videos/uploads/old-clip.mp4')
        ->thumbnail_path->not->toBe('thumbnails/uploads/old-clip.jpg');

    expect(Storage::disk('public')->exists('assets/videos/uploads/old-clip.mp4'))->toBeFalse()
        ->and(Storage::disk('public')->exists('assets/thumbnails/uploads/old-clip.jpg'))->toBeFalse()
        ->and(Storage::disk('public')->exists('assets/' . $video->fresh()->video_path))->toBeTrue()
        ->and(Storage::disk('public')->exists('assets/' . $video->fresh()->thumbnail_path))->toBeTrue();
});

test('admins can see the current thumbnail while editing a video in filament', function () {
    Storage::fake('public');
    Storage::fake('s3');

    $admin = User::factory()->create(['is_admin' => true]);
    $video = Video::factory()->create([
        'thumbnail_path' => 'thumbnails/uploads/current-thumb.jpg',
    ]);

    Storage::disk('public')->put('assets/thumbnails/uploads/current-thumb.jpg', 'thumbnail');

    $this->actingAs($admin);

    $thumbnailUrl = app(MediaUrlResolver::class)->resolveThumbnailUrl($video);

    Livewire::test(EditVideo::class, ['record' => $video->getKey()])
        ->assertSee('Current thumbnail')
        ->assertSee('thumbnails/uploads/current-thumb.jpg')
        ->assertSee($thumbnailUrl, false);
});

test('change password page keeps the submit action separated from the form fields', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(ChangePassword::class)
        ->assertSeeHtml('class="flex pt-5"');
});

test('admins can delete videos in filament and cleanup stored assets', function () {
    Storage::fake('public');
    Storage::fake('s3');

    $admin = User::factory()->create(['is_admin' => true]);
    $video = Video::factory()->create([
        'video_path' => 'videos/uploads/delete-me.mp4',
        'thumbnail_path' => 'thumbnails/uploads/delete-me.jpg',
    ]);

    Storage::disk('public')->put('assets/videos/uploads/delete-me.mp4', 'video');
    Storage::disk('public')->put('assets/thumbnails/uploads/delete-me.jpg', 'thumbnail');
    Storage::disk('s3')->put('assets/videos/uploads/delete-me.mp4', 'video');
    Storage::disk('s3')->put('assets/thumbnails/uploads/delete-me.jpg', 'thumbnail');

    $this->actingAs($admin);

    Livewire::test(EditVideo::class, ['record' => $video->getKey()])
        ->callAction(DeleteAction::class)
        ->assertHasNoActionErrors();

    expect(Video::query()->find($video->getKey()))->toBeNull()
        ->and(Storage::disk('public')->exists('assets/videos/uploads/delete-me.mp4'))->toBeFalse()
        ->and(Storage::disk('public')->exists('assets/thumbnails/uploads/delete-me.jpg'))->toBeFalse()
        ->and(Storage::disk('s3')->exists('assets/videos/uploads/delete-me.mp4'))->toBeFalse()
        ->and(Storage::disk('s3')->exists('assets/thumbnails/uploads/delete-me.jpg'))->toBeFalse();
});

test('admins can replace the shared profile image in filament', function () {
    Storage::fake('public');
    Storage::fake('s3');

    $admin = User::factory()->create(['is_admin' => true]);

    Storage::disk('public')->put('assets/profile/profile.jpg', 'old-public');
    Storage::disk('s3')->put('assets/profile/profile.jpg', 'old-remote');

    $this->actingAs($admin);

    Livewire::test(ManageBrandAssets::class)
        ->fillForm([
            'profile_image' => UploadedFile::fake()->image('profile.jpg'),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Storage::disk('public')->exists('assets/profile/profile.jpg'))->toBeTrue()
        ->and(Storage::disk('s3')->exists('assets/profile/profile.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->get('assets/profile/profile.jpg'))->not->toBe('old-public')
        ->and(Storage::disk('s3')->get('assets/profile/profile.jpg'))->not->toBe('old-remote');
});

test('admins can change their password directly in filament', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'password' => 'old-password',
    ]);

    $this->actingAs($admin);

    Livewire::test(ChangePassword::class)
        ->fillForm([
            'current_password' => 'old-password',
            'new_password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('new-password-123', $admin->fresh()->password))->toBeTrue();
});
