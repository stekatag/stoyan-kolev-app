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
        private readonly BucketDiskResolver $bucketDiskResolver,
        private readonly Filesystem $filesystem,
        private readonly MediaPathService $mediaPathService,
    ) {
    }

    public function sync(?callable $progressCallback = null): AssetSyncResult {
        $missingConfiguration = $this->missingBucketConfiguration();

        if ($missingConfiguration !== []) {
            throw new RuntimeException(
                'Bucket disk [' . $this->bucketDisk() . '] is not fully configured. Missing: ' . implode(', ', $missingConfiguration) . '.'
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

        $uploaded = Storage::disk($this->bucketDisk())->put($bucketKey, $contents, ['visibility' => 'public']);

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
            'key' => $this->bucketDiskConfigValue('key'),
            'secret' => $this->bucketDiskConfigValue('secret'),
            'bucket' => $this->bucketDiskConfigValue('bucket'),
            'endpoint' => $this->bucketDiskConfigValue('endpoint'),
            'region' => $this->bucketDiskConfigValue('region'),
        ];

        return collect($requiredConfiguration)
            ->filter(static fn($value): bool => !filled($value))
            ->keys()
            ->values()
            ->all();
    }

    private function bucketDiskConfigValue(string $key): string {
        return $this->bucketDiskResolver->configValue($key);
    }

    private function bucketDisk(): string {
        return $this->bucketDiskResolver->disk();
    }
}
