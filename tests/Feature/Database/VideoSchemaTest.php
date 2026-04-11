<?php

use Illuminate\Support\Facades\Schema;

test('videos table uses canonical media path columns', function () {
    expect(Schema::hasColumns('videos', [
        'video_path',
        'thumbnail_path',
        'mime_type',
        'original_filename',
        'source_type',
    ]))->toBeTrue()
        ->and(Schema::hasColumn('videos', 'bucket_video_key'))->toBeFalse()
        ->and(Schema::hasColumn('videos', 'local_video_path'))->toBeFalse()
        ->and(Schema::hasColumn('videos', 'bucket_thumbnail_key'))->toBeFalse()
        ->and(Schema::hasColumn('videos', 'local_thumbnail_path'))->toBeFalse()
        ->and(Schema::hasColumn('videos', 'video_storage_status'))->toBeFalse()
        ->and(Schema::hasColumn('videos', 'thumbnail_storage_status'))->toBeFalse()
        ->and(Schema::hasColumn('videos', 'duration_seconds'))->toBeFalse();
});
