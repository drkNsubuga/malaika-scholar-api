<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Virus Scanning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure virus scanning for uploaded files. Available methods:
    | - basic: File type and content validation only
    | - clamav: ClamAV antivirus integration
    | - virustotal: VirusTotal API integration
    | - aws_guardduty: AWS GuardDuty Malware Protection
    |
    */

    'virus_scan' => [
        'method' => env('VIRUS_SCAN_METHOD', 'basic'),
        
        'clamav' => [
            'socket' => env('CLAMAV_SOCKET', '/var/run/clamav/clamd.ctl'),
            'timeout' => env('CLAMAV_TIMEOUT', 30),
        ],
        
        'virustotal' => [
            'api_key' => env('VIRUSTOTAL_API_KEY'),
            'timeout' => env('VIRUSTOTAL_TIMEOUT', 60),
        ],
        
        'aws_guardduty' => [
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_MALWARE_SCAN_BUCKET'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Security settings for file uploads including size limits,
    | allowed file types, and content validation rules.
    |
    */

    'file_upload' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10485760), // 10MB in bytes
        'max_files_per_request' => env('MAX_FILES_PER_REQUEST', 5),
        
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ],
        
        'allowed_extensions' => [
            'pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'txt'
        ],
        
        'blocked_extensions' => [
            'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 
            'jar', 'php', 'py', 'rb', 'sh', 'pl', 'asp', 'aspx'
        ],
        
        'scan_content' => env('SCAN_FILE_CONTENT', true),
        'quarantine_suspicious' => env('QUARANTINE_SUSPICIOUS_FILES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for file uploads to prevent abuse.
    |
    */

    'rate_limiting' => [
        'uploads_per_minute' => env('UPLOADS_PER_MINUTE', 10),
        'uploads_per_hour' => env('UPLOADS_PER_HOUR', 100),
        'uploads_per_day' => env('UPLOADS_PER_DAY', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | CSP headers for file serving and download endpoints.
    |
    */

    'csp' => [
        'file_download' => "default-src 'none'; frame-ancestors 'none';",
        'image_display' => "default-src 'none'; img-src 'self'; frame-ancestors 'none';",
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary File Cleanup
    |--------------------------------------------------------------------------
    |
    | Settings for automatic cleanup of temporary files.
    |
    */

    'temp_cleanup' => [
        'enabled' => env('TEMP_CLEANUP_ENABLED', true),
        'max_age_hours' => env('TEMP_MAX_AGE_HOURS', 24),
        'cleanup_schedule' => env('TEMP_CLEANUP_SCHEDULE', 'hourly'),
    ],

];