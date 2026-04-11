<?php

namespace App\Filament\Resources\Videos\Schemas;

use App\Models\Video;
use App\Services\Media\MediaPathService;
use App\Services\Media\MediaUrlResolver;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class VideoForm {
    public static function configure(Schema $schema): Schema {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Placeholder::make('current_thumbnail_preview')
                    ->label('Current thumbnail')
                    ->visible(fn(string $operation): bool => $operation === 'edit')
                    ->content(function (?Video $record): string|HtmlString {
                        if (!($record instanceof Video) || blank($record->thumbnail_path)) {
                            return 'No thumbnail uploaded yet.';
                        }

                        $thumbnailUrl = app(MediaUrlResolver::class)->resolveThumbnailUrl($record)
                            ?? app(MediaPathService::class)->publicUrl((string) $record->thumbnail_path);

                        return new HtmlString(
                            '<div style="display: flex; flex-direction: column; gap: 0.75rem;">'
                                . '<img src="' . e($thumbnailUrl) . '" alt="Current thumbnail" style="max-width: 12rem; border-radius: 0.75rem;" />'
                                . '<div style="font-size: 0.875rem; color: rgb(115 115 115);">' . e((string) $record->thumbnail_path) . '</div>'
                                . '</div>'
                        );
                    })
                    ->columnSpanFull(),
                FileUpload::make('video')
                    ->required(fn(string $operation): bool => $operation === 'create'),
                FileUpload::make('thumbnail')
                    ->image(),
                Toggle::make('is_visible')
                    ->default(true)
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
