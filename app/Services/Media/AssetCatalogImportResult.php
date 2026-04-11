<?php

namespace App\Services\Media;

class AssetCatalogImportResult {
    public function __construct(
        public int $categoriesCreated = 0,
        public int $videosCreated = 0,
        public int $videosUpdated = 0,
        public int $publicCopies = 0,
    ) {
    }
}
