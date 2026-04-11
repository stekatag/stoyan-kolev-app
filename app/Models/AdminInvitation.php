<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminInvitation extends Model {
    use HasFactory;

    protected $fillable = [
        'email',
        'token_hash',
        'expires_at',
        'consumed_at',
        'created_by_user_id',
    ];

    protected function casts(): array {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
