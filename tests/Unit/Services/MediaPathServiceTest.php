<?php

use App\Services\Media\MediaPathService;

test('media path service derives one shared storage path from canonical paths', function () {
    $service = app(MediaPathService::class);

    expect($service->storagePath('videos/qdosan/only-clip.mp4'))->toBe('assets/videos/qdosan/only-clip.mp4')
        ->and($service->storagePath('assets/screenshots/qdosan.png'))->toBe('assets/screenshots/qdosan.png')
        ->and($service->publicUrl('homepage/initial screen.png'))->toBe('/storage/assets/homepage/initial%20screen.png');
});

test('media path service resolves configured canonical asset paths', function () {
    $service = app(MediaPathService::class);

    expect($service->configuredAssetCanonicalPath('homepage_image'))->toBe('homepage/initial screen.png')
        ->and($service->configuredAssetCanonicalPath('intro_video'))->toBe('intro/Intro.mp4')
        ->and($service->configuredAssetCanonicalPath('profile_image'))->toBe('profile/profile.jpg');
});
