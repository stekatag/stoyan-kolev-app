<?php

namespace Database\Factories;

use App\Enums\AssetStorageStatus;
use App\Enums\VideoSourceType;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'category_id' => Category::factory(),
            'title' => fake()->unique()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'is_visible' => true,
            'sort_order' => fake()->numberBetween(0, 20),
            'bucket_video_key' => 'videos/' . fake()->uuid() . '.mp4',
            'local_video_path' => 'assets/videos/' . fake()->slug() . '.mp4',
            'bucket_thumbnail_key' => 'thumbnails/' . fake()->uuid() . '.jpg',
            'local_thumbnail_path' => 'assets/screenshots/' . fake()->slug() . '.jpg',
            'video_storage_status' => AssetStorageStatus::BucketAndLocal,
            'thumbnail_storage_status' => AssetStorageStatus::BucketAndLocal,
            'source_type' => VideoSourceType::ImportedAsset,
            'original_filename' => fake()->slug() . '.mp4',
            'mime_type' => 'video/mp4',
            'duration_seconds' => fake()->numberBetween(10, 600),
        ];
    }
}
