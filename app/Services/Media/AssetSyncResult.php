<?php

namespace App\Services\Media;

class AssetSyncResult {
    public function __construct(
        public int $filesDiscovered = 0,
        public int $bucketUploads = 0,
        public int $recoveryCopies = 0,
        public int $failedUploads = 0,
    ) {
    }

    public function toArray(): array {
        return [
            'files_discovered' => $this->filesDiscovered,
            'bucket_uploads' => $this->bucketUploads,
            'recovery_copies' => $this->recoveryCopies,
            'failed_uploads' => $this->failedUploads,
        ];
    }
}
