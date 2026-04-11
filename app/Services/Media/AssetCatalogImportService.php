<?php

namespace App\Services\Media;

use App\Enums\HomepageHotspotKey;
use App\Enums\VideoSourceType;
use App\Models\Category;
use App\Models\Video;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\VideoRepositoryInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetCatalogImportService {
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly Filesystem $filesystem,
        private readonly MediaPathService $mediaPathService,
    ) {
    }

    public function import(): AssetCatalogImportResult {
        $result = new AssetCatalogImportResult;
        $videosRoot = (string) config('stoyan_kolev.import.backup_videos_root');
        $thumbnailsRoot = (string) config('stoyan_kolev.import.backup_thumbnails_root');
        $screenshotsRoot = (string) config('stoyan_kolev.import.backup_screenshots_root');

        foreach ($this->filesystem->directories($videosRoot) as $categoryDirectory) {
            $slug = basename($categoryDirectory);
            $previewImagePath = $this->resolveCategoryPreviewPath($slug, $thumbnailsRoot, $screenshotsRoot, $result);
            $category = $this->categoryRepository->firstOrCreateBySlug($slug, [
                'name' => Str::headline($slug),
                'description' => null,
                'is_visible' => true,
                'sort_order' => 0,
                'homepage_hotspot_key' => HomepageHotspotKey::tryFrom($slug)?->value,
                'modal_preview_image_path' => $previewImagePath,
            ]);

            if ($category->wasRecentlyCreated) {
                $result->categoriesCreated++;
            } else {
                $category = $this->syncCategoryPreviewPath($category, $previewImagePath);
            }

            foreach ($this->videoFilesForCategory($categoryDirectory) as $index => $file) {
                $extension = strtolower($file->getExtension());

                if (!in_array($extension, ['mp4', 'mov', 'm4v'], true)) {
                    continue;
                }

                $title = Str::headline(pathinfo($file->getFilename(), PATHINFO_FILENAME));
                $slugBase = Str::slug($slug . '-' . pathinfo($file->getFilename(), PATHINFO_FILENAME));
                $videoPath = 'videos/' . $slug . '/' . $file->getFilename();
                $thumbnailPath = $this->resolveVideoThumbnailPath($slug, $index + 1, $thumbnailsRoot, $screenshotsRoot, $result);

                $this->copyToPublicDisk($file->getPathname(), $videoPath, $result);

                $attributes = [
                    'category_id' => $category->getKey(),
                    'title' => $title,
                    'slug' => $slugBase,
                    'description' => null,
                    'is_visible' => true,
                    'sort_order' => $index,
                    'video_path' => $videoPath,
                    'thumbnail_path' => $thumbnailPath,
                    'source_type' => VideoSourceType::ImportedAsset,
                    'original_filename' => $file->getFilename(),
                    'mime_type' => 'video/mp4',
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
        $this->copyConfiguredAssetToPublic('profile_image', $result);

        return $result;
    }

    /**
     * @return Collection<int, \SplFileInfo>
     */
    private function videoFilesForCategory(string $categoryDirectory): Collection {
        return collect($this->filesystem->files($categoryDirectory))
            ->sortBy(fn($file) => mb_strtolower($file->getFilename()))
            ->values();
    }

    private function copyConfiguredAssetToPublic(string $configKey, AssetCatalogImportResult $result): void {
        $backupPath = base_path((string) config("stoyan_kolev.{$configKey}.backup_path"));
        $canonicalPath = $this->mediaPathService->configuredAssetCanonicalPath($configKey);

        if ($this->filesystem->exists($backupPath)) {
            $this->copyToPublicDisk($backupPath, $canonicalPath, $result);
        }
    }

    private function syncCategoryPreviewPath(Category $category, ?string $previewImagePath): Category {
        if ($category->modal_preview_image_path === $previewImagePath) {
            return $category;
        }

        return $this->categoryRepository->update($category, [
            'modal_preview_image_path' => $previewImagePath,
        ]);
    }

    private function resolveCategoryPreviewPath(
        string $slug,
        string $thumbnailsRoot,
        string $screenshotsRoot,
        AssetCatalogImportResult $result,
    ): ?string {
        return $this->resolveNumberedThumbnailPath($slug, 1, $thumbnailsRoot, $result)
            ?? $this->resolveLegacyScreenshotPath($slug, $screenshotsRoot, $result);
    }

    private function resolveVideoThumbnailPath(
        string $slug,
        int $sortOrder,
        string $thumbnailsRoot,
        string $screenshotsRoot,
        AssetCatalogImportResult $result,
    ): ?string {
        return $this->resolveNumberedThumbnailPath($slug, $sortOrder, $thumbnailsRoot, $result)
            ?? $this->resolveLegacyScreenshotPath($slug, $screenshotsRoot, $result);
    }

    private function resolveNumberedThumbnailPath(
        string $slug,
        int $sortOrder,
        string $thumbnailsRoot,
        AssetCatalogImportResult $result,
    ): ?string {
        $thumbnailDirectory = $thumbnailsRoot . DIRECTORY_SEPARATOR . $slug;

        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $candidate = $thumbnailDirectory . DIRECTORY_SEPARATOR . $sortOrder . '.' . $extension;

            if (!$this->filesystem->exists($candidate)) {
                continue;
            }

            $thumbnailPath = 'thumbnails/' . $slug . '/' . $sortOrder . '.' . $extension;
            $this->copyToPublicDisk($candidate, $thumbnailPath, $result);

            return $thumbnailPath;
        }

        return null;
    }

    private function resolveLegacyScreenshotPath(string $slug, string $screenshotsRoot, AssetCatalogImportResult $result): ?string {
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
            $candidate = $screenshotsRoot . DIRECTORY_SEPARATOR . $slug . '.' . $extension;

            if (!$this->filesystem->exists($candidate)) {
                continue;
            }

            $previewPath = 'screenshots/' . $slug . '.' . $extension;
            $this->copyToPublicDisk($candidate, $previewPath, $result);

            return $previewPath;
        }

        return null;
    }

    private function copyToPublicDisk(string $sourcePath, string $canonicalPath, AssetCatalogImportResult $result): void {
        Storage::disk('public')->put($this->mediaPathService->storagePath($canonicalPath), $this->filesystem->get($sourcePath));
        $result->publicCopies++;
    }
}
