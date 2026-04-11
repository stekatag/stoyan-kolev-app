<?php

namespace App\Repositories;

use App\Models\Video;
use App\Repositories\Contracts\VideoRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentVideoRepository implements VideoRepositoryInterface {
    public function create(array $attributes): Video {
        return Video::query()->create($attributes);
    }

    public function update(Video $video, array $attributes): Video {
        $video->fill($attributes)->save();

        return $video->refresh();
    }

    public function firstOrCreateBySlug(string $slug, array $attributes = []): Video {
        return Video::query()->firstOrCreate(['slug' => $slug], $attributes);
    }

    public function getVisibleByCategoryId(int $categoryId): Collection {
        return Video::query()
            ->where('category_id', $categoryId)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
