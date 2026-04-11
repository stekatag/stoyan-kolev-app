<?php

namespace App\Filament\Resources\AdminInvitations;

use App\Filament\Resources\AdminInvitations\Pages\CreateAdminInvitation;
use App\Filament\Resources\AdminInvitations\Pages\ListAdminInvitations;
use App\Filament\Resources\AdminInvitations\Schemas\AdminInvitationForm;
use App\Filament\Resources\AdminInvitations\Tables\AdminInvitationsTable;
use App\Models\AdminInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdminInvitationResource extends Resource {
    protected static ?string $model = AdminInvitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema {
        return AdminInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table {
        return AdminInvitationsTable::configure($table);
    }

    public static function getRelations(): array {
        return [
            //
        ];
    }

    public static function getPages(): array {
        return [
            'index' => ListAdminInvitations::route('/'),
            'create' => CreateAdminInvitation::route('/create'),
        ];
    }
}
