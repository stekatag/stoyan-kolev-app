<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminBrandAssetService {
    public function __construct(
        private readonly MediaPathService $mediaPathService,
    ) {
    }

    public function replaceProfileImage(UploadedFile $profileImage): void {
        $canonicalPath = $this->mediaPathService->configuredAssetCanonicalPath('profile_image');
        $storagePath = $this->mediaPathService->storagePath($canonicalPath);

        Storage::disk('public')->putFileAs(
            dirname($storagePath),
            $profileImage,
            basename($canonicalPath),
        );

        Storage::disk(config('stoyan_kolev.bucket_disk'))->putFileAs(
            dirname($storagePath),
            $profileImage,
            basename($canonicalPath),
            ['visibility' => 'public'],
        );
    }
}
