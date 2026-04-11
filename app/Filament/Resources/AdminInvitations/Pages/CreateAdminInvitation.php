<?php

namespace App\Filament\Resources\AdminInvitations\Pages;

use App\Filament\Resources\AdminInvitations\AdminInvitationResource;
use App\Models\AdminInvitation;
use App\Models\User;
use App\Services\Auth\AdminInvitationService;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreateAdminInvitation extends CreateRecord {
    protected static string $resource = AdminInvitationResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): AdminInvitation {
        /** @var User $user */
        $user = auth()->user();

        try {
            $result = app(AdminInvitationService::class)->createInvitation(
                $data['email'],
                $user,
                Carbon::parse($data['expires_at']),
            );
        } catch (DomainException $exception) {
            throw ValidationException::withMessages([
                'data.email' => match ($exception->getMessage()) {
                    'already_admin' => 'That email already belongs to an admin.',
                    default => 'That email is unavailable for invitation.',
                },
            ]);
        }

        Notification::make()
            ->title('Invitation created')
            ->body($result['signedUrl'])
            ->success()
            ->send();

        return $result['invitation'];
    }
}
