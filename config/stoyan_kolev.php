<?php

return [
    'bucket_disk' => env('STOYAN_BUCKET_DISK', 's3'),
    'recovery_disk' => env('STOYAN_RECOVERY_DISK', 'stoyan_recovery'),
    'homepage_image' => [
        'bucket_key' => env('STOYAN_HOMEPAGE_IMAGE_BUCKET_KEY', 'stoyan/homepage/initial screen.png'),
        'public_path' => env('STOYAN_HOMEPAGE_IMAGE_PUBLIC_PATH', 'assets/initial screen/initial screen.png'),
        'backup_path' => env('STOYAN_HOMEPAGE_IMAGE_BACKUP_PATH', 'assets/initial screen/initial screen.png'),
    ],
    'intro_video' => [
        'bucket_key' => env('STOYAN_INTRO_VIDEO_BUCKET_KEY', 'stoyan/intro/Intro.mp4'),
        'public_path' => env('STOYAN_INTRO_VIDEO_PUBLIC_PATH', 'assets/videos/Intro.mp4'),
        'backup_path' => env('STOYAN_INTRO_VIDEO_BACKUP_PATH', 'assets/videos/Intro.mp4'),
    ],
    'profile_image' => [
        'bucket_key' => env('STOYAN_PROFILE_IMAGE_BUCKET_KEY', 'stoyan/profile/qdosan.png'),
        'public_path' => env('STOYAN_PROFILE_IMAGE_PUBLIC_PATH', 'assets/screenshots/qdosan.png'),
        'backup_path' => env('STOYAN_PROFILE_IMAGE_BACKUP_PATH', 'assets/screenshots/qdosan.png'),
    ],
    'import' => [
        'backup_videos_root' => base_path('assets/videos'),
        'backup_screenshots_root' => base_path('assets/screenshots'),
        'ignored_files' => ['.DS_Store'],
    ],
    'hotspots' => [
        [
            'key' => 'qdosan',
            'label' => 'Qdosan',
            'x' => 4.0,
            'y' => 20.2,
            'width' => 27.2,
            'height' => 28.2,
        ],
        [
            'key' => 'kaish',
            'label' => 'Kaish',
            'x' => 65.4,
            'y' => 20.2,
            'width' => 28.0,
            'height' => 28.2,
        ],
        [
            'key' => 'uchuden',
            'label' => 'Uchuden',
            'x' => 35.3,
            'y' => 20.2,
            'width' => 25.8,
            'height' => 28.2,
        ],
        [
            'key' => 'vesel',
            'label' => 'Vesel',
            'x' => 35,
            'y' => 62.5,
            'width' => 27,
            'height' => 29,
        ],
        [
            'key' => 'izmoren',
            'label' => 'Izmoren',
            'x' => 4.0,
            'y' => 63.8,
            'width' => 27.2,
            'height' => 28.2,
        ],
        [
            'key' => 'izpederastql',
            'label' => 'Izpederastql',
            'x' => 66.3,
            'y' => 63.2,
            'width' => 28,
            'height' => 28.2,
        ],
    ],
];
