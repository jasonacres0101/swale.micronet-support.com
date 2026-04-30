<?php

return [
    'enabled' => (bool) env('CAMERA_EMAIL_INGEST_ENABLED', false),
    'host' => env('CAMERA_EMAIL_HOST'),
    'port' => (int) env('CAMERA_EMAIL_PORT', 993),
    'encryption' => env('CAMERA_EMAIL_ENCRYPTION', 'ssl'),
    'validate_cert' => (bool) env('CAMERA_EMAIL_VALIDATE_CERT', true),
    'mailbox' => env('CAMERA_EMAIL_MAILBOX', 'INBOX'),
    'username' => env('CAMERA_EMAIL_USERNAME'),
    'password' => env('CAMERA_EMAIL_PASSWORD'),
    'delete_after_import' => (bool) env('CAMERA_EMAIL_DELETE_AFTER_IMPORT', false),
    'mark_seen_after_import' => (bool) env('CAMERA_EMAIL_MARK_SEEN_AFTER_IMPORT', true),
];
