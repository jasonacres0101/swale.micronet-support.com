<?php

return [
    'upload_disk' => env('MAINTENANCE_UPLOAD_DISK', 'public'),
    'upload_directory' => env('MAINTENANCE_UPLOAD_DIRECTORY', 'maintenance'),
    'max_upload_kb' => (int) env('MAINTENANCE_MAX_UPLOAD_KB', 5120),
];
