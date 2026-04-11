<?php

namespace App\Filament\Resources\AdminInvitations\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdminInvitationsTable {
    public static function configure(Table $table): Table {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('consumed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('creator.email')
                    ->label('Created by')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
