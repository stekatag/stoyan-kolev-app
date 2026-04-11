<?php

namespace App\Services\Media;

use App\Enums\AssetStorageStatus;
use App\Models\Video;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AssetSyncService {
    public function __construct(
        private readonly Filesystem $filesystem,
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
        $this->syncVideos($result, $progressCallback);

        return $result;
    }

    private function syncConfiguredAsset(string $configKey, AssetSyncResult $result, ?callable $progressCallback = null): void {
        $localPath = Storage::disk('public')->path((string) config("stoyan_kolev.{$configKey}.public_path"));
        $bucketKey = (string) config("stoyan_kolev.{$configKey}.bucket_key");

        if ($this->filesystem->exists($localPath)) {
            $this->mirrorFile($localPath, $bucketKey, $result, $progressCallback);
        }
    }

    private function syncVideos(AssetSyncResult $result, ?callable $progressCallback = null): void {
        /** @var Collection<int, Video> $videos */
        $videos = Video::query()->whereNotNull('local_video_path')->get();

        foreach ($videos as $video) {
            $localVideoPath = Storage::disk('public')->path((string) $video->local_video_path);

            if ($this->filesystem->exists($localVideoPath) && filled($video->bucket_video_key)) {
                $uploaded = $this->mirrorFile($localVideoPath, (string) $video->bucket_video_key, $result, $progressCallback);

                if ($uploaded) {
                    $video->forceFill([
                        'video_storage_status' => AssetStorageStatus::BucketAndLocal,
                    ])->save();
                }
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

        Storage::disk(config('stoyan_kolev.recovery_disk'))->put($bucketKey, $contents);
        $result->recoveryCopies++;

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
