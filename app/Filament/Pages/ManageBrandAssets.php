<?php

namespace App\Filament\Pages;

use App\Services\Media\AdminBrandAssetService;
use App\Services\Media\MediaUrlResolver;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ManageBrandAssets extends Page implements HasForms {
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Brand Assets';

    protected string $view = 'filament.pages.manage-brand-assets';

    public ?array $data = [];

    public function mount(): void {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema {
        $imageUrl = app(MediaUrlResolver::class)->resolveConfiguredAssetUrl('profile_image');

        return $schema
            ->components([
                Placeholder::make('current_profile_image')
                    ->label('Current profile image')
                    ->content($imageUrl === null
                        ? 'No shared profile image is available yet.'
                        : new HtmlString('<img src="' . e($imageUrl) . '" alt="Current profile image" style="max-width: 10rem; border-radius: 0.75rem;" />')),
                FileUpload::make('profile_image')
                    ->label('Profile image / favicon')
                    ->image()
                    ->required()
                    ->helperText('This single image is used for the public profile photo and both favicons.'),
            ])
            ->statePath('data');
    }

    public function save(): void {
        $state = $this->form->getState();
        $profileImage = $this->normalizeUpload($state['profile_image'] ?? null);

        if ($profileImage === null) {
            return;
        }

        app(AdminBrandAssetService::class)->replaceProfileImage($profileImage);
        $this->form->fill();

        Notification::make()
            ->title('Brand assets updated.')
            ->success()
            ->send();
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
