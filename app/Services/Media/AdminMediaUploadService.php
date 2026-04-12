<?php

namespace App\Services\Media;

use App\Enums\VideoSourceType;
use App\Models\Video;
use App\Repositories\Contracts\VideoRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AdminMediaUploadService {
    public function __construct(
        private readonly BucketDiskResolver $bucketDiskResolver,
        private readonly VideoRepositoryInterface $videoRepository,
        private readonly MediaPathService $mediaPathService,
    ) {
    }

    public function storeVideoUpload(
        ?UploadedFile $videoUpload,
        int $categoryId,
        string $title,
        ?UploadedFile $thumbnailUpload = null,
        bool $isVisible = true,
        int $sortOrder = 0,
    ): Video {
        if ($videoUpload === null) {
            throw new \InvalidArgumentException('A video upload is required.');
        }

        $slug = $this->uniqueSlug($title);
        $storedVideo = $this->storeVideoAsset($slug, $videoUpload);
        $thumbnailPath = $thumbnailUpload === null ? null : $this->storeThumbnailAsset($slug, $thumbnailUpload);

        return $this->videoRepository->create([
            'category_id' => $categoryId,
            'title' => $title,
            'slug' => $slug,
            'description' => null,
            'is_visible' => $isVisible,
            'sort_order' => $sortOrder,
            'video_path' => $storedVideo['path'],
            'thumbnail_path' => $thumbnailPath,
            'source_type' => VideoSourceType::AdminUpload,
            'original_filename' => $storedVideo['original_filename'],
            'mime_type' => $storedVideo['mime_type'],
        ]);
    }

    public function updateVideo(
        Video $video,
        int $categoryId,
        string $title,
        ?UploadedFile $videoUpload = null,
        ?UploadedFile $thumbnailUpload = null,
        bool $isVisible = true,
        int $sortOrder = 0,
    ): Video {
        $attributes = [
            'category_id' => $categoryId,
            'title' => $title,
            'description' => $video->description,
            'is_visible' => $isVisible,
            'sort_order' => $sortOrder,
        ];

        if ($videoUpload !== null) {
            $slug = $this->uniqueSlug($title);
            $this->deleteCanonicalAsset($video->video_path);
            $storedVideo = $this->storeVideoAsset($slug, $videoUpload);

            $attributes['slug'] = $slug;
            $attributes['video_path'] = $storedVideo['path'];
            $attributes['original_filename'] = $storedVideo['original_filename'];
            $attributes['mime_type'] = $storedVideo['mime_type'];
            $attributes['source_type'] = VideoSourceType::AdminUpload;
        }

        if ($thumbnailUpload !== null) {
            $slug = $attributes['slug'] ?? $video->slug;
            $this->deleteCanonicalAsset($video->thumbnail_path);
            $attributes['thumbnail_path'] = $this->storeThumbnailAsset($slug, $thumbnailUpload);
        }

        return $this->videoRepository->update($video, $attributes);
    }

    public function deleteVideo(Video $video): void {
        $this->deleteCanonicalAsset($video->video_path);
        $this->deleteCanonicalAsset($video->thumbnail_path);

        $video->delete();
    }

    private function sanitizeOriginalFileName(string $originalFileName, string $slug, string $extension): string {
        if (!mb_check_encoding($originalFileName, 'UTF-8')) {
            return $slug . '.' . $extension;
        }

        $normalizedFileName = trim($originalFileName);

        if ($normalizedFileName === '') {
            return $slug . '.' . $extension;
        }

        return Str::limit($normalizedFileName, 255, '');
    }

    private function uniqueSlug(string $title): string {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $suffix = 2;

        while (Video::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array{path: string, original_filename: string, mime_type: string}
     */
    private function storeVideoAsset(string $slug, UploadedFile $videoUpload): array {
        $videoFileName = $slug . '.' . $videoUpload->getClientOriginalExtension();
        $videoPath = 'videos/uploads/' . $videoFileName;

        $this->storeCanonicalAsset($videoPath, $videoUpload);

        return [
            'path' => $videoPath,
            'original_filename' => $this->sanitizeOriginalFileName($videoUpload->getClientOriginalName(), $slug, $videoUpload->getClientOriginalExtension()),
            'mime_type' => $videoUpload->getClientMimeType() ?: 'video/mp4',
        ];
    }

    private function storeThumbnailAsset(string $slug, UploadedFile $thumbnailUpload): string {
        $thumbnailFileName = $slug . '.' . $thumbnailUpload->getClientOriginalExtension();
        $thumbnailPath = 'thumbnails/uploads/' . $thumbnailFileName;

        $this->storeCanonicalAsset($thumbnailPath, $thumbnailUpload);

        return $thumbnailPath;
    }

    private function storeCanonicalAsset(string $canonicalPath, UploadedFile $upload): void {
        $storagePath = $this->mediaPathService->storagePath($canonicalPath);

        Storage::disk('public')->putFileAs(
            dirname($storagePath),
            $upload,
            basename($canonicalPath),
        );

        Storage::disk($this->bucketDiskResolver->disk())->putFileAs(
            dirname($storagePath),
            $upload,
            basename($canonicalPath),
            ['visibility' => 'public'],
        );
    }

    private function deleteCanonicalAsset(?string $canonicalPath): void {
        if (blank($canonicalPath)) {
            return;
        }

        $storagePath = $this->mediaPathService->storagePath($canonicalPath);

        Storage::disk('public')->delete($storagePath);

        try {
            Storage::disk($this->bucketDiskResolver->disk())->delete($storagePath);
        } catch (Throwable) {
            // Best-effort remote cleanup only.
        }
    }
}
