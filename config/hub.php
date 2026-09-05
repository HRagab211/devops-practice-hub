<?php

return [
    'version' => env('APP_VERSION', 'local'),
    'commit' => env('APP_COMMIT_SHA', 'unreleased'),
    'instance' => env('APP_INSTANCE', 'local'),
    'lab_enabled' => env('LAB_TOOLS_ENABLED', env('APP_ENV', 'production') === 'local'),
    'avatar_disk' => env('PROFILE_IMAGE_DISK', 'public'),
    'report_disk' => env('REPORT_DISK', 'reports'),
];
