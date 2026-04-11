<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface {
    public function create(array $attributes): Category {
        return Category::query()->create($attributes);
    }

    public function update(Category $category, array $attributes): Category {
        $category->fill($attributes)->save();

        return $category->refresh();
    }

    public function firstOrCreateBySlug(string $slug, array $attributes = []): Category {
        return Category::query()->firstOrCreate(['slug' => $slug], $attributes);
    }

    public function findVisibleByHotspotKey(string $hotspotKey): ?Category {
        return Category::query()
            ->where('homepage_hotspot_key', $hotspotKey)
            ->where('is_visible', true)
            ->with(['videos' => fn($query) => $query->where('is_visible', true)->orderBy('sort_order')->orderBy('id')])
            ->first();
    }

    public function getVisibleHomepageCategories(): Collection {
        return Category::query()
            ->where('is_visible', true)
            ->whereNotNull('homepage_hotspot_key')
            ->with(['videos' => fn($query) => $query->where('is_visible', true)->orderBy('sort_order')->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
