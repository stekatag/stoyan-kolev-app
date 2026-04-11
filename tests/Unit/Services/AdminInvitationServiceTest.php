<?php

use App\Models\User;
use App\Services\Auth\AdminInvitationService;

test('admin invitation service creates a signed invitation link and completes it', function () {
    $creator = User::factory()->create(['is_admin' => true]);
    $service = app(AdminInvitationService::class);

    $result = $service->createInvitation('invitee@example.com', $creator, now()->addDay());

    expect($result['signedUrl'])->toContain('/admin/invitations/')
        ->and($service->findActiveInvitation($result['token']))->not->toBeNull();

    $user = $service->completeInvitation($result['token'], [
        'name' => 'Invited Admin',
        'password' => 'password123!',
    ]);

    expect($user->is_admin)->toBeTrue()
        ->and($service->findActiveInvitation($result['token']))->toBeNull();
});
