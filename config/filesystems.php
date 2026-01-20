<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // AWS S3 Configuration
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        // Document Storage (configurable between local and cloud)
        'documents' => [
            'driver' => env('DOCUMENTS_STORAGE_DRIVER', 'local'),
            'root' => storage_path('app/documents'),
            'visibility' => 'private',
            'serve' => true,
            'throw' => false,
            'report' => false,
            // S3 specific settings (used when driver is 's3')
            'key' => env('DOCUMENTS_AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('DOCUMENTS_AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('DOCUMENTS_AWS_DEFAULT_REGION', env('AWS_DEFAULT_REGION')),
            'bucket' => env('DOCUMENTS_AWS_BUCKET', env('AWS_BUCKET')),
            'url' => env('DOCUMENTS_AWS_URL', env('AWS_URL')),
            'endpoint' => env('DOCUMENTS_AWS_ENDPOINT', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => env('DOCUMENTS_AWS_USE_PATH_STYLE_ENDPOINT', env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
        ],

        // Avatar/Profile Images Storage
        'avatars' => [
            'driver' => env('AVATARS_STORAGE_DRIVER', 'public'),
            'root' => storage_path('app/public/avatars'),
            'url' => rtrim(env('APP_URL'), '/').'/storage/avatars',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
            // S3 specific settings (used when driver is 's3')
            'key' => env('AVATARS_AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('AVATARS_AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('AVATARS_AWS_DEFAULT_REGION', env('AWS_DEFAULT_REGION')),
            'bucket' => env('AVATARS_AWS_BUCKET', env('AWS_BUCKET')),
            'url' => env('AVATARS_AWS_URL', env('AWS_URL')),
            'endpoint' => env('AVATARS_AWS_ENDPOINT', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => env('AVATARS_AWS_USE_PATH_STYLE_ENDPOINT', env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
        ],

        // Temporary Files Storage (for uploads in progress)
        'temp' => [
            'driver' => 'local',
            'root' => storage_path('app/temp'),
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
        ],

        // Backup Storage (can be different from main storage)
        'backups' => [
            'driver' => env('BACKUP_STORAGE_DRIVER', 's3'),
            'root' => storage_path('app/backups'),
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
            // S3 specific settings
            'key' => env('BACKUP_AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
            'secret' => env('BACKUP_AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
            'region' => env('BACKUP_AWS_DEFAULT_REGION', env('AWS_DEFAULT_REGION')),
            'bucket' => env('BACKUP_AWS_BUCKET', env('AWS_BUCKET')),
            'url' => env('BACKUP_AWS_URL', env('AWS_URL')),
            'endpoint' => env('BACKUP_AWS_ENDPOINT', env('AWS_ENDPOINT')),
            'use_path_style_endpoint' => env('BACKUP_AWS_USE_PATH_STYLE_ENDPOINT', env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
        ],

        // Google Cloud Storage (alternative cloud provider)
        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            'key_file' => env('GOOGLE_CLOUD_KEY_FILE'), // Path to service account JSON
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''),
            'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI'),
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
        ],

        // Azure Blob Storage (alternative cloud provider)
        'azure' => [
            'driver' => 'azure',
            'name' => env('AZURE_STORAGE_NAME'),
            'key' => env('AZURE_STORAGE_KEY'),
            'container' => env('AZURE_STORAGE_CONTAINER'),
            'url' => env('AZURE_STORAGE_URL'),
            'prefix' => env('AZURE_STORAGE_PREFIX', ''),
            'throw' => false,
            'report' => false,
        ],

        // DigitalOcean Spaces (S3-compatible)
        'spaces' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'region' => env('DO_SPACES_REGION'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'url' => env('DO_SPACES_URL'),
            'use_path_style_endpoint' => false,
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
