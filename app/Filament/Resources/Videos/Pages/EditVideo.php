<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use App\Models\Video;
use App\Services\Media\AdminMediaUploadService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class EditVideo extends EditRecord {
    protected static string $resource = VideoResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model {
        /** @var Video $record */
        return app(AdminMediaUploadService::class)->updateVideo(
            $record,
            categoryId: (int) $data['category_id'],
            title: $data['title'],
            videoUpload: $this->normalizeUpload($data['video'] ?? null),
            thumbnailUpload: $this->normalizeUpload($data['thumbnail'] ?? null),
            isVisible: (bool) ($data['is_visible'] ?? true),
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }

    protected function getHeaderActions(): array {
        return [
            DeleteAction::make()
                ->action(function (): void {
                    app(AdminMediaUploadService::class)->deleteVideo($this->getRecord());
                })
                ->successRedirectUrl(VideoResource::getUrl()),
        ];
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
