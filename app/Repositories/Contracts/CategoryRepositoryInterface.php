<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface {
    public function create(array $attributes): Category;

    public function update(Category $category, array $attributes): Category;

    public function firstOrCreateBySlug(string $slug, array $attributes = []): Category;

    public function findVisibleByHotspotKey(string $hotspotKey): ?Category;

    public function getVisibleHomepageCategories(): Collection;
}
