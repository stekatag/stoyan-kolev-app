<?php

namespace App\Repositories;

use App\Models\AdminInvitation;
use App\Repositories\Contracts\AdminInvitationRepositoryInterface;

class EloquentAdminInvitationRepository implements AdminInvitationRepositoryInterface {
    public function create(array $attributes): AdminInvitation {
        return AdminInvitation::query()->create($attributes);
    }

    public function findActiveByToken(string $token): ?AdminInvitation {
        return AdminInvitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findActiveByEmail(string $email): ?AdminInvitation {
        return AdminInvitation::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();
    }

    public function markConsumed(AdminInvitation $invitation): AdminInvitation {
        $invitation->forceFill([
            'consumed_at' => now(),
        ])->save();

        return $invitation->refresh();
    }
}
