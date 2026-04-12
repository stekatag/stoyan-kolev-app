<?php

namespace App\Services\Media;

class BucketDiskResolver {
    public function disk(): string {
        $defaultFilesystemDisk = (string) config('filesystems.default', '');

        if ($defaultFilesystemDisk !== '' && !in_array($defaultFilesystemDisk, ['local', 'public'], true)) {
            return $defaultFilesystemDisk;
        }

        return 's3';
    }

    public function configValue(string $key): string {
        return (string) config('filesystems.disks.' . $this->disk() . '.' . $key, '');
    }
}
