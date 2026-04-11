<?php

namespace App\Enums;

enum AssetStorageStatus: string {
    case Missing = 'missing';
    case LocalOnly = 'local_only';
    case BucketOnly = 'bucket_only';
    case BucketAndLocal = 'bucket_and_local';
}
