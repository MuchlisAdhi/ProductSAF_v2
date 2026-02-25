<?php

return [
    'enabled' => env('PUBLIC_TRACKER_ENABLED', true),
    'excluded_paths' => [
        'admin*',
        'api*',
        'login*',
        'logout*',
        'up*',
        '_debugbar*',
    ],
];
