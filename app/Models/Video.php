<?php

namespace App\Models;

use App\Enums\AssetStorageStatus;
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
        'bucket_video_key',
        'local_video_path',
        'bucket_thumbnail_key',
        'local_thumbnail_path',
        'video_storage_status',
        'thumbnail_storage_status',
        'source_type',
        'original_filename',
        'mime_type',
        'duration_seconds',
    ];

    protected function casts(): array {
        return [
            'is_visible' => 'boolean',
            'video_storage_status' => AssetStorageStatus::class,
            'thumbnail_storage_status' => AssetStorageStatus::class,
            'source_type' => VideoSourceType::class,
        ];
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }
}
