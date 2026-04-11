<?php

use App\Filament\Resources\AdminInvitations\Pages\CreateAdminInvitation;
use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

test('the admin invitations table exists with the expected columns', function () {
    expect(Schema::hasTable('admin_invitations'))->toBeTrue()
        ->and(Schema::hasColumns('admin_invitations', [
            'email',
            'token_hash',
            'expires_at',
            'consumed_at',
            'created_by_user_id',
        ]))->toBeTrue();
});

test('a valid invitation link renders the setup page with a locked email', function () {
    $token = 'test-token';

    $invitation = AdminInvitation::query()->create([
        'email' => 'invitee@example.com',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addDay(),
        'created_by_user_id' => User::factory()->create(['is_admin' => true])->getKey(),
    ]);

    $response = $this->get(URL::temporarySignedRoute(
        'admin.invitations.show',
        $invitation->expires_at,
        ['token' => $token],
    ));

    $response->assertOk()->assertInertia(
        fn($page) => $page
            ->component('auth/AdminInviteSetup')
            ->where('inviteState', 'valid')
            ->where('email', 'invitee@example.com')
            ->where('emailLocked', true)
    );
});

test('expired or tampered invitation links show a clear invalid state', function () {
    $this->get('/admin/invitations/bad-token?signature=bad')
        ->assertInertia(
            fn($page) => $page
                ->component('auth/AdminInviteSetup')
                ->where('inviteState', 'invalid')
        );
});

test('a valid invitation can be consumed only once', function () {
    $token = 'consume-me';

    $invitation = AdminInvitation::query()->create([
        'email' => 'consume@example.com',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addDay(),
        'created_by_user_id' => User::factory()->create(['is_admin' => true])->getKey(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'admin.invitations.store',
        $invitation->expires_at,
        ['token' => $token],
    );

    $this->post($signedUrl, [
        'name' => 'Admin User',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ])->assertRedirect('/admin');

    $this->post($signedUrl, [
        'name' => 'Admin User',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ])->assertInertia(fn($page) => $page->where('inviteState', 'consumed'));
});

test('accepting an invite for an existing non-admin email is rejected cleanly', function () {
    User::factory()->create([
        'email' => 'existing-user@example.com',
        'is_admin' => false,
    ]);

    $token = 'existing-user-token';
    $invitation = AdminInvitation::query()->create([
        'email' => 'existing-user@example.com',
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->addDay(),
        'created_by_user_id' => User::factory()->create(['is_admin' => true])->getKey(),
    ]);

    $this->post(URL::temporarySignedRoute(
        'admin.invitations.store',
        $invitation->expires_at,
        ['token' => $token],
    ), [
        'name' => 'Existing User',
        'password' => 'password123!',
        'password_confirmation' => 'password123!',
    ])->assertInertia(fn($page) => $page->where('inviteState', 'email_unavailable'));
});

test('admins can create invitations from filament', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin);

    Livewire::test(CreateAdminInvitation::class)
        ->fillForm([
            'email' => 'new-admin@example.com',
            'expires_at' => now()->addDay(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(AdminInvitation::query()->where('email', 'new-admin@example.com')->exists())->toBeTrue();
});
