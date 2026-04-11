<?php

namespace App\Services\Media;

use InvalidArgumentException;

class MediaPathService {
    public function storagePath(string $canonicalPath): string {
        $normalizedPath = ltrim($canonicalPath, '/');

        if (str_starts_with($normalizedPath, 'assets/')) {
            $normalizedPath = substr($normalizedPath, strlen('assets/'));
        }

        return 'assets/' . $normalizedPath;
    }

    public function publicUrl(string $canonicalPath): string {
        $segments = array_map(rawurlencode(...), explode('/', ltrim($this->storagePath($canonicalPath), '/')));

        return '/storage/' . implode('/', $segments);
    }

    public function configuredAssetCanonicalPath(string $configKey): string {
        $canonicalPath = (string) config("stoyan_kolev.{$configKey}.canonical_path");

        if ($canonicalPath === '') {
            throw new InvalidArgumentException("Configured asset [{$configKey}] does not define a canonical_path.");
        }

        return $canonicalPath;
    }
}
