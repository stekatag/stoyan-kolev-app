<?php

namespace App\Enums;

enum VideoSourceType: string {
    case ImportedAsset = 'imported_asset';
    case AdminUpload = 'admin_upload';
}
