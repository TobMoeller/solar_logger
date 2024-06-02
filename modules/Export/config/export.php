<?php

return [
    'create_entries' => [
        'enabled' => env('EXPORT_CREATE_ENTRIES_ENABLED', false),
    ],
    'export_to_server' => [
        'enabled' => env('EXPORT_TO_SERVER_ENABLED', false),
        'base_url' => env('EXPORT_TO_SERVER_BASE_URL', 'http://127.0.0.1:8000'),
        'token' => env('EXPORT_TO_SERVER_TOKEN'),
        'timeout' => env('EXPORT_TO_SERVER_TIMEOUT', 20),
        'connect_timeout' => env('EXPORT_TO_SERVER_CONNECT_TIMEOUT', 5),
    ],
];
