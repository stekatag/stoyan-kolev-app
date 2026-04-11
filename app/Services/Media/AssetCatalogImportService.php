<?php

namespace App\Services\Media;

use App\Enums\AssetStorageStatus;
use App\Enums\HomepageHotspotKey;
use App\Enums\VideoSourceType;
use App\Models\Video;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\VideoRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetCatalogImportService {
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function import(): AssetCatalogImportResult {
        $result = new AssetCatalogImportResult;
        $videosRoot = (string) config('stoyan_kolev.import.backup_videos_root');
        $screenshotsRoot = (string) config('stoyan_kolev.import.backup_screenshots_root');

        foreach ($this->filesystem->directories($videosRoot) as $categoryDirectory) {
            $slug = basename($categoryDirectory);
            $category = $this->categoryRepository->firstOrCreateBySlug($slug, [
                'name' => Str::headline($slug),
                'description' => null,
                'is_visible' => true,
                'sort_order' => 0,
                'homepage_hotspot_key' => HomepageHotspotKey::tryFrom($slug)?->value,
                'modal_preview_image_path' => $this->importPreviewImage($slug, $screenshotsRoot, $result),
            ]);

            if ($category->wasRecentlyCreated) {
                $result->categoriesCreated++;
            }

            foreach ($this->filesystem->files($categoryDirectory) as $index => $file) {
                $extension = strtolower($file->getExtension());

                if (!in_array($extension, ['mp4', 'mov', 'm4v'], true)) {
                    continue;
                }

                $title = Str::headline(pathinfo($file->getFilename(), PATHINFO_FILENAME));
                $slugBase = Str::slug($slug . '-' . pathinfo($file->getFilename(), PATHINFO_FILENAME));
                $localVideoPath = 'assets/videos/' . $slug . '/' . $file->getFilename();
                $bucketVideoKey = 'stoyan/videos/' . $slug . '/' . $file->getFilename();
                $localThumbnailPath = $this->guessPublicThumbnailPath($slug, $screenshotsRoot);
                $bucketThumbnailKey = $localThumbnailPath === null ? null : 'stoyan/screenshots/' . basename($localThumbnailPath);

                $this->copyToPublicDisk($file->getPathname(), $localVideoPath, $result);

                $attributes = [
                    'category_id' => $category->getKey(),
                    'title' => $title,
                    'slug' => $slugBase,
                    'description' => null,
                    'is_visible' => true,
                    'sort_order' => $index,
                    'bucket_video_key' => $bucketVideoKey,
                    'local_video_path' => $localVideoPath,
                    'bucket_thumbnail_key' => $bucketThumbnailKey,
                    'local_thumbnail_path' => $localThumbnailPath,
                    'video_storage_status' => AssetStorageStatus::LocalOnly,
                    'thumbnail_storage_status' => $localThumbnailPath === null ? AssetStorageStatus::Missing : AssetStorageStatus::LocalOnly,
                    'source_type' => VideoSourceType::ImportedAsset,
                    'original_filename' => $file->getFilename(),
                    'mime_type' => 'video/mp4',
                    'duration_seconds' => null,
                ];

                $existingVideo = Video::query()->where('slug', $slugBase)->first();

                if ($existingVideo === null) {
                    $this->videoRepository->create($attributes);
                    $result->videosCreated++;

                    continue;
                }

                $this->videoRepository->update($existingVideo, $attributes);
                $result->videosUpdated++;
            }
        }

        $this->copyConfiguredAssetToPublic('homepage_image', $result);
        $this->copyConfiguredAssetToPublic('intro_video', $result);

        return $result;
    }

    private function copyConfiguredAssetToPublic(string $configKey, AssetCatalogImportResult $result): void {
        $backupPath = base_path((string) config("stoyan_kolev.{$configKey}.backup_path"));
        $publicPath = (string) config("stoyan_kolev.{$configKey}.public_path");

        if ($this->filesystem->exists($backupPath)) {
            $this->copyToPublicDisk($backupPath, $publicPath, $result);
        }
    }

    private function importPreviewImage(string $slug, string $screenshotsRoot, AssetCatalogImportResult $result): ?string {
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $candidate = $screenshotsRoot . DIRECTORY_SEPARATOR . $slug . '.' . $extension;

            if (!$this->filesystem->exists($candidate)) {
                continue;
            }

            $publicPath = 'assets/screenshots/' . $slug . '.' . $extension;
            $this->copyToPublicDisk($candidate, $publicPath, $result);

            return $publicPath;
        }

        return null;
    }

    private function guessPublicThumbnailPath(string $slug, string $screenshotsRoot): ?string {
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $candidate = $screenshotsRoot . DIRECTORY_SEPARATOR . $slug . '.' . $extension;

            if ($this->filesystem->exists($candidate)) {
                return 'assets/screenshots/' . $slug . '.' . $extension;
            }
        }

        return null;
    }

    private function copyToPublicDisk(string $sourcePath, string $destinationPath, AssetCatalogImportResult $result): void {
        Storage::disk('public')->put($destinationPath, $this->filesystem->get($sourcePath));
        $result->publicCopies++;
    }
}
