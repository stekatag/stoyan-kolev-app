<?php

namespace App\Repositories\Contracts;

use App\Models\Video;
use Illuminate\Support\Collection;

interface VideoRepositoryInterface {
    public function create(array $attributes): Video;

    public function update(Video $video, array $attributes): Video;

    public function firstOrCreateBySlug(string $slug, array $attributes = []): Video;

    public function getVisibleByCategoryId(int $categoryId): Collection;
}
