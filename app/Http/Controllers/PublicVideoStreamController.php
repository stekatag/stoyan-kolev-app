<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\Media\MediaPathService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicVideoStreamController extends Controller {
    public function __construct(
        private readonly MediaPathService $mediaPathService,
    ) {
    }

    public function __invoke(Video $video): BinaryFileResponse {
        abort_unless(filled($video->video_path), 404);

        $path = storage_path('app/public/' . $this->mediaPathService->storagePath((string) $video->video_path));

        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Accept-Ranges' => 'bytes',
            'Content-Type' => $video->mime_type ?: 'video/mp4',
        ]);
    }
}
