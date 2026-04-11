<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord {
    protected static string $resource = CategoryResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array {
        CategoryResource::assertVisibleHotspotAvailability(
            $data['homepage_hotspot_key'] ?? null,
            (bool) ($data['is_visible'] ?? false),
            $this->getRecord()->getKey(),
        );

        return $data;
    }

    protected function getHeaderActions(): array {
        return [
            DeleteAction::make(),
        ];
    }
}
