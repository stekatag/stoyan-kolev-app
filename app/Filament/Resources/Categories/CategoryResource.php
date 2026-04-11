<?php

namespace App\Filament\Resources\Categories;

use App\Enums\HomepageHotspotKey;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class CategoryResource extends Resource {
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table {
        return CategoriesTable::configure($table);
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function hotspotOptions(): array {
        $options = [];

        foreach (HomepageHotspotKey::cases() as $hotspotKey) {
            $options[$hotspotKey->value] = $hotspotKey->value;
        }

        return $options;
    }

    public static function assertVisibleHotspotAvailability(?string $hotspotKey, bool $isVisible, ?int $ignoreCategoryId = null): void {
        if (blank($hotspotKey) || !$isVisible) {
            return;
        }

        $query = Category::query()
            ->where('homepage_hotspot_key', $hotspotKey)
            ->where('is_visible', true);

        if ($ignoreCategoryId !== null) {
            $query->whereKeyNot($ignoreCategoryId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'data.homepage_hotspot_key' => 'The selected hotspot is already assigned to another visible category.',
            ]);
        }
    }
}
