<?php

namespace App\Filament\Resources\AdminInvitations\Pages;

use App\Filament\Resources\AdminInvitations\AdminInvitationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdminInvitations extends ListRecords {
    protected static string $resource = AdminInvitationResource::class;

    protected function getHeaderActions(): array {
        return [
            CreateAction::make(),
        ];
    }
}
