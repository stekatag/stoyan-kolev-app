<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\Media\MediaUrlResolver;

class PublicCatalogService {
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly MediaUrlResolver $mediaUrlResolver,
    ) {
    }

    public function buildHomepagePayload(): array {
        $categories = $this->categoryRepository->getVisibleHomepageCategories();
        $categoriesByHotspot = [];

        foreach ($categories as $category) {
            $videos = $category->videos;
            $firstVideo = $videos->first();

            $categoriesByHotspot[$category->homepage_hotspot_key] = [
                'id' => $category->getKey(),
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'previewImageUrl' => $this->previewImageUrl($category->modal_preview_image_path),
                'videoCount' => $videos->count(),
                'opensDirectly' => $videos->count() === 1,
                'videos' => $videos->map(fn($video) => [
                    'id' => $video->getKey(),
                    'title' => $video->title,
                    'slug' => $video->slug,
                    'description' => $video->description,
                    'videoUrl' => $this->mediaUrlResolver->resolveVideoUrl($video),
                    'thumbnailUrl' => $this->mediaUrlResolver->resolveThumbnailUrl($video),
                ])->values()->all(),
                'directVideo' => $firstVideo === null ? null : [
                    'id' => $firstVideo->getKey(),
                    'title' => $firstVideo->title,
                    'slug' => $firstVideo->slug,
                    'description' => $firstVideo->description,
                    'videoUrl' => $this->mediaUrlResolver->resolveVideoUrl($firstVideo),
                    'thumbnailUrl' => $this->mediaUrlResolver->resolveThumbnailUrl($firstVideo),
                ],
            ];
        }

        return [
            'introVideoUrl' => $this->mediaUrlResolver->resolveConfiguredAssetUrl(
                (string) config('stoyan_kolev.intro_video.bucket_key'),
                (string) config('stoyan_kolev.intro_video.public_path'),
            ),
            'profileImageUrl' => $this->mediaUrlResolver->resolveConfiguredAssetUrl(
                (string) config('stoyan_kolev.profile_image.bucket_key'),
                (string) config('stoyan_kolev.profile_image.public_path'),
            ),
            'homepageImageUrl' => $this->mediaUrlResolver->resolveConfiguredAssetUrl(
                (string) config('stoyan_kolev.homepage_image.bucket_key'),
                (string) config('stoyan_kolev.homepage_image.public_path'),
            ),
            'hotspots' => config('stoyan_kolev.hotspots', []),
            'categoriesByHotspot' => $categoriesByHotspot,
        ];
    }

    private function previewImageUrl(?string $path): ?string {
        if ($path === null || $path === '') {
            return null;
        }

        $segments = array_map(rawurlencode(...), explode('/', ltrim($path, '/')));

        return '/storage/' . implode('/', $segments);
    }
}
