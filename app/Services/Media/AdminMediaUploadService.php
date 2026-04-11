<?php

namespace App\Services\Media;

use App\Enums\AssetStorageStatus;
use App\Enums\VideoSourceType;
use App\Models\Video;
use App\Repositories\Contracts\VideoRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminMediaUploadService {
    public function __construct(private readonly VideoRepositoryInterface $videoRepository) {
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
        $originalFileName = $this->sanitizeOriginalFileName($videoUpload->getClientOriginalName(), $slug, $videoUpload->getClientOriginalExtension());
        $videoFileName = $slug . '.' . $videoUpload->getClientOriginalExtension();
        $bucketVideoKey = 'stoyan/uploads/videos/' . $videoFileName;
        $recoveryVideoKey = $bucketVideoKey;

        Storage::disk(config('stoyan_kolev.bucket_disk'))->putFileAs('stoyan/uploads/videos', $videoUpload, $videoFileName, ['visibility' => 'public']);
        Storage::disk(config('stoyan_kolev.recovery_disk'))->putFileAs('stoyan/uploads/videos', $videoUpload, $videoFileName);

        $bucketThumbnailKey = null;
        $localThumbnailPath = null;
        $thumbnailStatus = AssetStorageStatus::Missing;

        if ($thumbnailUpload !== null) {
            $thumbnailFileName = $slug . '.' . $thumbnailUpload->getClientOriginalExtension();
            $bucketThumbnailKey = 'stoyan/uploads/thumbnails/' . $thumbnailFileName;
            Storage::disk(config('stoyan_kolev.bucket_disk'))->putFileAs('stoyan/uploads/thumbnails', $thumbnailUpload, $thumbnailFileName, ['visibility' => 'public']);
            Storage::disk(config('stoyan_kolev.recovery_disk'))->putFileAs('stoyan/uploads/thumbnails', $thumbnailUpload, $thumbnailFileName);
            $localThumbnailPath = 'storage/stoyan-recovery/stoyan/uploads/thumbnails/' . $thumbnailFileName;
            $thumbnailStatus = AssetStorageStatus::BucketAndLocal;
        }

        return $this->videoRepository->create([
            'category_id' => $categoryId,
            'title' => $title,
            'slug' => $slug,
            'description' => null,
            'is_visible' => $isVisible,
            'sort_order' => $sortOrder,
            'bucket_video_key' => $bucketVideoKey,
            'local_video_path' => 'storage/stoyan-recovery/' . $recoveryVideoKey,
            'bucket_thumbnail_key' => $bucketThumbnailKey,
            'local_thumbnail_path' => $localThumbnailPath,
            'video_storage_status' => AssetStorageStatus::BucketAndLocal,
            'thumbnail_storage_status' => $thumbnailStatus,
            'source_type' => VideoSourceType::AdminUpload,
            'original_filename' => $originalFileName,
            'mime_type' => $videoUpload->getClientMimeType(),
            'duration_seconds' => null,
        ]);
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
}
