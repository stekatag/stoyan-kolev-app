<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible',
        'sort_order',
        'homepage_hotspot_key',
        'modal_preview_image_path',
    ];

    protected function casts(): array {
        return [
            'is_visible' => 'boolean',
        ];
    }

    public function videos(): HasMany {
        return $this->hasMany(Video::class);
    }
}
