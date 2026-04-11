<?php

namespace App\Services\Auth;

use App\Models\AdminInvitation;
use App\Models\User;
use App\Repositories\Contracts\AdminInvitationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use DomainException;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AdminInvitationService {
    public function __construct(
        private readonly AdminInvitationRepositoryInterface $adminInvitationRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function createInvitation(string $email, User $creator, \DateTimeInterface $expiresAt): array {
        $existingUser = $this->userRepository->findByEmail($email);

        if ($existingUser?->is_admin) {
            throw new DomainException('already_admin');
        }

        if ($existingUser !== null || $this->adminInvitationRepository->findActiveByEmail($email) !== null) {
            throw new DomainException('email_unavailable');
        }

        $token = Str::random(64);

        $invitation = $this->adminInvitationRepository->create([
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => $expiresAt,
            'created_by_user_id' => $creator->getKey(),
        ]);

        return [
            'invitation' => $invitation,
            'token' => $token,
            'signedUrl' => URL::temporarySignedRoute('admin.invitations.show', $expiresAt, ['token' => $token]),
        ];
    }

    public function findActiveInvitation(string $token): ?AdminInvitation {
        return $this->adminInvitationRepository->findActiveByToken($token);
    }

    public function completeInvitation(string $token, array $attributes): User {
        $invitation = $this->findActiveInvitation($token);

        if ($invitation === null) {
            $inactiveInvitation = AdminInvitation::query()->where('token_hash', hash('sha256', $token))->first();

            if ($inactiveInvitation?->consumed_at !== null) {
                throw new DomainException('consumed');
            }

            if ($inactiveInvitation !== null && $inactiveInvitation->expires_at->isPast()) {
                throw new DomainException('invalid');
            }

            throw new DomainException('invalid');
        }

        if ($this->userRepository->findByEmail($invitation->email) !== null) {
            throw new DomainException('email_unavailable');
        }

        $user = $this->userRepository->createAdmin([
            'name' => $attributes['name'],
            'email' => $invitation->email,
            'password' => $attributes['password'],
        ]);

        $this->adminInvitationRepository->markConsumed($invitation);

        return $user;
    }
}
