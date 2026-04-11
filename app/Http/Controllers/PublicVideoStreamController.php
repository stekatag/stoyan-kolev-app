<?php

namespace App\Http\Controllers;

use App\Enums\AssetStorageStatus;
use App\Models\Video;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicVideoStreamController extends Controller {
    public function __invoke(Video $video): BinaryFileResponse {
        abort_unless(
            in_array($video->video_storage_status, [AssetStorageStatus::LocalOnly, AssetStorageStatus::BucketAndLocal], true)
                && filled($video->local_video_path),
            404,
        );

        $path = storage_path('app/public/' . ltrim((string) $video->local_video_path, '/'));

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Accept-Ranges' => 'bytes',
            'Content-Type' => $video->mime_type ?: 'video/mp4',
        ]);
    }
}
