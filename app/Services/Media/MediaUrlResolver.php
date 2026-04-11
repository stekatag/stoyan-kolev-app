<?php

namespace App\Services\Media;

use App\Models\Video;
use Illuminate\Support\Facades\Storage;

class MediaUrlResolver {
    public function __construct(
        private readonly MediaPathService $mediaPathService,
    ) {
    }

    public function resolveAssetUrl(?string $canonicalPath): ?string {
        return $this->resolveCanonicalAssetUrl($canonicalPath);
    }

    public function resolveVideoUrl(Video $video): ?string {
        if (blank($video->video_path)) {
            return null;
        }

        if ($this->canBuildBucketUrl()) {
            return $this->bucketUrl($this->mediaPathService->storagePath((string) $video->video_path));
        }

        if ($this->localPublicAssetExists((string) $video->video_path)) {
            return route('videos.stream', $video, false);
        }

        return null;
    }

    public function resolveThumbnailUrl(Video $video): ?string {
        return $this->resolveCanonicalAssetUrl($video->thumbnail_path);
    }

    public function resolveConfiguredAssetUrl(string $configKey): string {
        return $this->resolveCanonicalAssetUrl($this->mediaPathService->configuredAssetCanonicalPath($configKey)) ?? '';
    }

    private function resolveCanonicalAssetUrl(?string $canonicalPath): ?string {
        if (blank($canonicalPath)) {
            return null;
        }

        if ($this->canBuildBucketUrl()) {
            return $this->bucketUrl($this->mediaPathService->storagePath($canonicalPath));
        }

        if ($this->localPublicAssetExists($canonicalPath)) {
            return $this->mediaPathService->publicUrl($canonicalPath);
        }

        return null;
    }

    private function localPublicAssetExists(string $canonicalPath): bool {
        return Storage::disk('public')->exists($this->mediaPathService->storagePath($canonicalPath));
    }

    private function canBuildBucketUrl(): bool {
        return
            (string) config('filesystems.disks.s3.url') !== ''
            || (
                (string) config('filesystems.disks.s3.endpoint') !== ''
                && (string) config('filesystems.disks.s3.bucket') !== ''
            );
    }

    private function bucketUrl(string $bucketKey): string {
        $configuredUrl = rtrim((string) config('filesystems.disks.s3.url'), '/');

        if ($configuredUrl !== '') {
            return $configuredUrl . '/' . ltrim($bucketKey, '/');
        }

        $endpoint = rtrim((string) config('filesystems.disks.s3.endpoint'), '/');
        $bucket = (string) config('filesystems.disks.s3.bucket');

        if ($endpoint !== '' && $bucket !== '') {
            return $endpoint . '/' . $bucket . '/' . ltrim($bucketKey, '/');
        }

        return '/' . ltrim($bucketKey, '/');
    }
}
