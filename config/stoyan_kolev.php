<?php

return [
    'bucket_disk' => env('STOYAN_BUCKET_DISK', 's3'),
    'homepage_image' => [
        'canonical_path' => env('STOYAN_HOMEPAGE_IMAGE_CANONICAL_PATH', 'homepage/initial screen.png'),
        'backup_path' => env('STOYAN_HOMEPAGE_IMAGE_BACKUP_PATH', 'assets/initial screen/initial screen.png'),
    ],
    'intro_video' => [
        'canonical_path' => env('STOYAN_INTRO_VIDEO_CANONICAL_PATH', 'intro/Intro.mp4'),
        'backup_path' => env('STOYAN_INTRO_VIDEO_BACKUP_PATH', 'assets/videos/Intro.mp4'),
    ],
    'profile_image' => [
        'canonical_path' => env('STOYAN_PROFILE_IMAGE_CANONICAL_PATH', 'profile/profile.jpg'),
        'backup_path' => env('STOYAN_PROFILE_IMAGE_BACKUP_PATH', 'assets/profile/profile.jpg'),
    ],
    'import' => [
        'backup_videos_root' => base_path('assets/videos'),
        'backup_thumbnails_root' => base_path('assets/thumbnails'),
        'ignored_files' => ['.DS_Store'],
        'video_order_overrides' => [
            'qdosan' => [
                'STOYAN_KOLEV_E_BESEN_CHE_NQMA_WESTER_UNION.mp4',
                'Stoyan Kolev psuva I otkacha s machete.mp4',
                'Стоян Колев тряска ребро.mp4',
            ],
            'kaish' => [
                'Stoyan Kolev Visualised.mp4',
                'stoyan kolev is my fighter.mp4',
                'СТОЯН КОЛЕВ ОВЛАДЯ ТЪМНАТА ЕНЕРГИЯ.mp4',
                'Стоян Колев ЧУПИ И ЯДЕ ТЕЛЕФОНА 19.mp4',
            ],
            'vesel' => [
                'Stoyan Kolev reketira stopadjii.mp4',
                'Стоян Колев вилнее във фитнеса.mp4',
                'Стоян Колев на лостове.mp4',
                'Стоян Колев с брадвата прай въртележки.mp4',
                'Стоян Колев се ебава с циганин.mp4',
                'дебелия.mp4',

            ],
        ],
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
