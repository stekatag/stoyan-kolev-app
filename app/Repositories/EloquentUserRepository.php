<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface {
    public function findByEmail(string $email): ?User {
        return User::query()->where('email', $email)->first();
    }

    public function createAdmin(array $attributes): User {
        $attributes['is_admin'] = true;

        return User::query()->create($attributes);
    }
}
