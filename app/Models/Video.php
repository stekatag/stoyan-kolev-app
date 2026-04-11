<?php

namespace App\Models;

use App\Enums\VideoSourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model {
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'is_visible',
        'sort_order',
        'video_path',
        'thumbnail_path',
        'source_type',
        'original_filename',
        'mime_type',
    ];

    protected function casts(): array {
        return [
            'is_visible' => 'boolean',
            'source_type' => VideoSourceType::class,
        ];
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }
}
