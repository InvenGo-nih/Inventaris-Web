<?php

return [
    'name' => 'InvenGo',
    'manifest' => [
        'name' => env('APP_NAME', 'InvenGo'),
        'short_name' => 'InvenGo',
        'start_url' => '/',
        'background_color' => '#ffffff',
        'theme_color' => '#000000',
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            '72x72' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
            '384x384' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/images/invenGo-logo.jpg',
                'purpose' => 'any'
            ],
        ],
        'splash' => [
            '640x1136' => '/images/invenGo-logo.jpg',
            '750x1334' => '/images/invenGo-logo.jpg',
            '828x1792' => '/images/invenGo-logo.jpg',
            '1125x2436' => '/images/invenGo-logo.jpg',
            '1242x2208' => '/images/invenGo-logo.jpg',
            '1242x2688' => '/images/invenGo-logo.jpg',
            '1536x2048' => '/images/invenGo-logo.jpg',
            '1668x2224' => '/images/invenGo-logo.jpg',
            '1668x2388' => '/images/invenGo-logo.jpg',
            '2048x2732' => '/images/invenGo-logo.jpg',
        ],
        'shortcuts' => [
            [
                'name' => 'Shortcut Link 1',
                'description' => 'Shortcut Link 1 Description',
                'url' => '/shortcutlink1',
                'icons' => [
                    "src" => "/images/icons/icon-72x72.png",
                    "purpose" => "any"
                ]
            ],
            [
                'name' => 'Shortcut Link 2',
                'description' => 'Shortcut Link 2 Description',
                'url' => '/shortcutlink2'
            ]
        ],
        'custom' => []
    ]
];
