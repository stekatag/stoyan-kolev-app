<?php

namespace Database\Factories;

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
            'video_path' => 'videos/' . fake()->slug() . '/' . fake()->uuid() . '.mp4',
            'thumbnail_path' => 'thumbnails/' . fake()->slug() . '.jpg',
            'source_type' => VideoSourceType::ImportedAsset,
            'original_filename' => fake()->slug() . '.mp4',
            'mime_type' => 'video/mp4',
        ];
    }
}
