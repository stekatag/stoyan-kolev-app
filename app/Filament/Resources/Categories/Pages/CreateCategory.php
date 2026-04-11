<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord {
    protected static string $resource = CategoryResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array {
        CategoryResource::assertVisibleHotspotAvailability(
            $data['homepage_hotspot_key'] ?? null,
            (bool) ($data['is_visible'] ?? false),
        );

        return $data;
    }
}
