<?php

namespace App\Services\Media;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AssetSyncService {
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly MediaPathService $mediaPathService,
    ) {
    }

    public function sync(?callable $progressCallback = null): AssetSyncResult {
        $missingConfiguration = $this->missingBucketConfiguration();

        if ($missingConfiguration !== []) {
            throw new RuntimeException(
                'S3 bucket storage is not fully configured. Missing: ' . implode(', ', $missingConfiguration) . '.'
            );
        }

        $result = new AssetSyncResult;
        $this->syncConfiguredAsset('homepage_image', $result, $progressCallback);
        $this->syncConfiguredAsset('intro_video', $result, $progressCallback);
        $this->syncConfiguredAsset('profile_image', $result, $progressCallback);
        $this->syncCategoryPreviews($result, $progressCallback);
        $this->syncVideos($result, $progressCallback);

        return $result;
    }

    private function syncConfiguredAsset(string $configKey, AssetSyncResult $result, ?callable $progressCallback = null): void {
        $canonicalPath = $this->mediaPathService->configuredAssetCanonicalPath($configKey);
        $storagePath = $this->mediaPathService->storagePath($canonicalPath);
        $localPath = Storage::disk('public')->path($storagePath);

        if ($this->filesystem->exists($localPath)) {
            $this->mirrorFile($localPath, $storagePath, $result, $progressCallback);
        }
    }

    private function syncVideos(AssetSyncResult $result, ?callable $progressCallback = null): void {
        /** @var Collection<int, Video> $videos */
        $videos = Video::query()->whereNotNull('video_path')->get();

        foreach ($videos as $video) {
            $storagePath = $this->mediaPathService->storagePath((string) $video->video_path);
            $localVideoPath = Storage::disk('public')->path($storagePath);

            if ($this->filesystem->exists($localVideoPath)) {
                $this->mirrorFile(
                    $localVideoPath,
                    $storagePath,
                    $result,
                    $progressCallback,
                );
            }

            if (filled($video->thumbnail_path)) {
                $storagePath = $this->mediaPathService->storagePath((string) $video->thumbnail_path);
                $localThumbnailPath = Storage::disk('public')->path($storagePath);

                if ($this->filesystem->exists($localThumbnailPath)) {
                    $this->mirrorFile(
                        $localThumbnailPath,
                        $storagePath,
                        $result,
                        $progressCallback,
                    );
                }
            }
        }
    }

    private function syncCategoryPreviews(AssetSyncResult $result, ?callable $progressCallback = null): void {
        /** @var Collection<int, Category> $categories */
        $categories = Category::query()->whereNotNull('modal_preview_image_path')->get();

        foreach ($categories as $category) {
            $storagePath = $this->mediaPathService->storagePath((string) $category->modal_preview_image_path);
            $localPreviewPath = Storage::disk('public')->path($storagePath);

            if ($this->filesystem->exists($localPreviewPath)) {
                $this->mirrorFile(
                    $localPreviewPath,
                    $storagePath,
                    $result,
                    $progressCallback,
                );
            }
        }
    }

    private function mirrorFile(string $sourcePath, string $bucketKey, AssetSyncResult $result, ?callable $progressCallback = null): bool {
        $result->filesDiscovered++;

        if ($progressCallback !== null) {
            $progressCallback($bucketKey);
        }

        $contents = $this->filesystem->get($sourcePath);

        $uploaded = Storage::disk(config('stoyan_kolev.bucket_disk'))->put($bucketKey, $contents, ['visibility' => 'public']);

        if ($uploaded !== true) {
            $result->failedUploads++;

            return false;
        }

        $result->bucketUploads++;

        return true;
    }

    /**
     * @return array<int, string>
     */
    private function missingBucketConfiguration(): array {
        $requiredConfiguration = [
            'AWS_ACCESS_KEY_ID' => config('filesystems.disks.s3.key'),
            'AWS_SECRET_ACCESS_KEY' => config('filesystems.disks.s3.secret'),
            'AWS_BUCKET' => config('filesystems.disks.s3.bucket'),
            'AWS_ENDPOINT' => config('filesystems.disks.s3.endpoint'),
            'AWS_DEFAULT_REGION' => config('filesystems.disks.s3.region'),
        ];

        return collect($requiredConfiguration)
            ->filter(static fn($value): bool => !filled($value))
            ->keys()
            ->values()
            ->all();
    }
}
