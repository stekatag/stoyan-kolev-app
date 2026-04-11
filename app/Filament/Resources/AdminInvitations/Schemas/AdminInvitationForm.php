<?php

namespace App\Filament\Resources\AdminInvitations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdminInvitationForm {
    public static function configure(Schema $schema): Schema {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('expires_at')
                    ->required()
                    ->default(now()->addDay()),
            ]);
    }
}
