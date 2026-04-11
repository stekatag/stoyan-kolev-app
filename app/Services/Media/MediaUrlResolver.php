<?php

namespace App\Services\Media;

use App\Enums\AssetStorageStatus;
use App\Models\Video;

class MediaUrlResolver {
    public function resolveVideoUrl(Video $video): ?string {
        if ($this->shouldUseBucketUrl($video->video_storage_status, $video->bucket_video_key)) {
            return $this->bucketUrl((string) $video->bucket_video_key);
        }

        if ($this->shouldUseLocalUrl($video->video_storage_status, $video->local_video_path)) {
            return route('videos.stream', $video, false);
        }

        return null;
    }

    public function resolveThumbnailUrl(Video $video): ?string {
        return $this->resolveAssetUrl($video->thumbnail_storage_status, $video->bucket_thumbnail_key, $video->local_thumbnail_path);
    }

    public function resolveConfiguredAssetUrl(string $bucketKey, string $localPath): string {
        if ($bucketKey !== '' && $this->canBuildBucketUrl()) {
            return $this->bucketUrl($bucketKey);
        }

        return $this->resolveLocalPathUrl($localPath) ?? '';
    }

    private function resolveAssetUrl(AssetStorageStatus|string|null $status, ?string $bucketKey, ?string $localPath): ?string {
        if ($this->shouldUseBucketUrl($status, $bucketKey)) {
            return $this->bucketUrl($bucketKey);
        }

        if ($this->shouldUseLocalUrl($status, $localPath)) {
            return $this->resolveLocalPathUrl($localPath);
        }

        return null;
    }

    private function shouldUseBucketUrl(AssetStorageStatus|string|null $status, ?string $bucketKey): bool {
        $resolvedStatus = $status instanceof AssetStorageStatus ? $status : AssetStorageStatus::tryFrom((string) $status);

        return in_array($resolvedStatus, [AssetStorageStatus::BucketAndLocal, AssetStorageStatus::BucketOnly], true)
            && filled($bucketKey)
            && $this->canBuildBucketUrl();
    }

    private function shouldUseLocalUrl(AssetStorageStatus|string|null $status, ?string $localPath): bool {
        $resolvedStatus = $status instanceof AssetStorageStatus ? $status : AssetStorageStatus::tryFrom((string) $status);

        return in_array($resolvedStatus, [AssetStorageStatus::BucketAndLocal, AssetStorageStatus::LocalOnly], true)
            && filled($localPath);
    }

    private function resolveLocalPathUrl(?string $localPath): ?string {
        if (blank($localPath)) {
            return null;
        }

        $segments = array_map(rawurlencode(...), explode('/', ltrim((string) $localPath, '/')));

        return '/storage/' . implode('/', $segments);
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
