<?php

use App\Repositories\EloquentUserRepository;

test('user repository can create and find admins by email', function () {
    $repository = app(EloquentUserRepository::class);
    $email = fake()->unique()->safeEmail();

    $admin = $repository->createAdmin([
        'name' => 'Admin User',
        'email' => $email,
        'password' => 'password123!',
    ]);

    expect($admin->is_admin)->toBeTrue()
        ->and($repository->findByEmail($email)?->getKey())->toBe($admin->getKey());
});
