<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CompleteAdminInvitationRequest;
use App\Services\Auth\AdminInvitationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Inertia\Response;

class AdminInvitationAcceptanceController extends Controller {
    public function show(Request $request, string $token, AdminInvitationService $invitationService): Response {
        if (!$request->hasValidSignature()) {
            return $this->renderState('invalid');
        }

        $invitation = $invitationService->findActiveInvitation($token);

        if ($invitation === null) {
            return $this->renderState('invalid');
        }

        return $this->renderState(
            'valid',
            email: $invitation->email,
            emailLocked: true,
            submitUrl: URL::temporarySignedRoute(
                'admin.invitations.store',
                $invitation->expires_at,
                ['token' => $token],
            ),
        );
    }

    public function store(CompleteAdminInvitationRequest $request, string $token, AdminInvitationService $invitationService): Response|RedirectResponse {
        if (!$request->hasValidSignature()) {
            return $this->renderState('invalid');
        }

        try {
            $user = $invitationService->completeInvitation($token, $request->validated());
        } catch (DomainException $exception) {
            return $this->renderState($exception->getMessage());
        }

        Auth::login($user);

        return redirect('/admin');
    }

    private function renderState(string $inviteState, ?string $email = null, bool $emailLocked = false, ?string $submitUrl = null): Response {
        return Inertia::render('auth/AdminInviteSetup', [
            'title' => 'Admin Invite Setup',
            'description' => 'Finish setting up the invited admin account.',
            'inviteState' => $inviteState,
            'email' => $email,
            'emailLocked' => $emailLocked,
            'submitUrl' => $submitUrl,
        ]);
    }
}
