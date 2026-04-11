<?php

namespace App\Repositories\Contracts;

use App\Models\AdminInvitation;

interface AdminInvitationRepositoryInterface {
    public function create(array $attributes): AdminInvitation;

    public function findActiveByToken(string $token): ?AdminInvitation;

    public function findActiveByEmail(string $email): ?AdminInvitation;

    public function markConsumed(AdminInvitation $invitation): AdminInvitation;
}
