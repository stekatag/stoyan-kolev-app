<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use App\Models\Video;
use App\Services\Media\AdminMediaUploadService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateVideo extends CreateRecord {
    protected static string $resource = VideoResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Video {
        return app(AdminMediaUploadService::class)->storeVideoUpload(
            $this->normalizeUpload($data['video']),
            categoryId: (int) $data['category_id'],
            title: $data['title'],
            thumbnailUpload: $this->normalizeUpload($data['thumbnail'] ?? null),
            isVisible: (bool) ($data['is_visible'] ?? true),
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }

    private function normalizeUpload(mixed $value): ?UploadedFile {
        if ($value instanceof UploadedFile) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return TemporaryUploadedFile::createFromLivewire($value);
        }

        return null;
    }
}
