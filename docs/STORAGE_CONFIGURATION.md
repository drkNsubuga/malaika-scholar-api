# Storage Configuration Guide

This document explains how to configure different storage types for the Malaika Backend API.

## Overview

The application supports multiple storage drivers and can be configured to use different storage types for different purposes:

- **Documents**: Secure storage for application documents and files
- **Avatars**: Public storage for user profile images
- **Temporary**: Local storage for temporary files during upload process
- **Backups**: Long-term storage for system backups

## Supported Storage Drivers

### Local Storage
- **Driver**: `local`
- **Use Case**: Development, small deployments
- **Configuration**: Automatic, no additional setup required

### Amazon S3
- **Driver**: `s3`
- **Use Case**: Production, scalable cloud storage
- **Configuration**: Requires AWS credentials

### Google Cloud Storage
- **Driver**: `gcs`
- **Use Case**: Alternative cloud storage
- **Configuration**: Requires Google Cloud service account

### Azure Blob Storage
- **Driver**: `azure`
- **Use Case**: Microsoft Azure environments
- **Configuration**: Requires Azure storage account

### DigitalOcean Spaces
- **Driver**: `s3` (S3-compatible)
- **Use Case**: Cost-effective cloud storage
- **Configuration**: Requires DigitalOcean Spaces credentials

## Environment Configuration

### Basic Configuration

```env
# Default filesystem disk
FILESYSTEM_DISK=local

# Document storage (can be different from default)
DOCUMENTS_STORAGE_DRIVER=local
AVATARS_STORAGE_DRIVER=public
BACKUP_STORAGE_DRIVER=s3
```

### AWS S3 Configuration

```env
# Main AWS credentials
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com

# Separate credentials for documents (optional)
DOCUMENTS_STORAGE_DRIVER=s3
DOCUMENTS_AWS_ACCESS_KEY_ID=your_documents_access_key
DOCUMENTS_AWS_SECRET_ACCESS_KEY=your_documents_secret_key
DOCUMENTS_AWS_BUCKET=your-documents-bucket

# Separate credentials for avatars (optional)
AVATARS_STORAGE_DRIVER=s3
AVATARS_AWS_ACCESS_KEY_ID=your_avatars_access_key
AVATARS_AWS_SECRET_ACCESS_KEY=your_avatars_secret_key
AVATARS_AWS_BUCKET=your-avatars-bucket
```

### Google Cloud Storage Configuration

```env
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE=/path/to/service-account.json
GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket-name
```

### Azure Blob Storage Configuration

```env
AZURE_STORAGE_NAME=your_storage_account
AZURE_STORAGE_KEY=your_storage_key
AZURE_STORAGE_CONTAINER=your_container_name
AZURE_STORAGE_URL=https://youraccount.blob.core.windows.net
```

### DigitalOcean Spaces Configuration

```env
DO_SPACES_KEY=your_spaces_key
DO_SPACES_SECRET=your_spaces_secret
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=your-space-name
DO_SPACES_URL=https://your-space.nyc3.digitaloceanspaces.com
```

## Storage Management Commands

### Check Storage Configuration

```bash
# View all storage configuration
php artisan storage:info

# View specific disk configuration
php artisan storage:info --disk=documents

# Test connectivity to all disks
php artisan storage:info --test

# Test specific disk connectivity
php artisan storage:info --test --disk=s3
```

### Cleanup Temporary Files

```bash
# Clean up temporary files older than 24 hours
php artisan storage:cleanup-temp

# Dry run to see what would be deleted
php artisan storage:cleanup-temp --dry-run

# Clean up files older than specific hours
php artisan storage:cleanup-temp --hours=48
```

## Usage in Code

### Using the Storage Service

```php
use App\Services\StorageService;

class DocumentController extends Controller
{
    public function upload(Request $request, StorageService $storage)
    {
        $file = $request->file('document');
        
        // Store document
        $result = $storage->storeDocument($file, 'applications');
        
        if ($result['success']) {
            // File stored successfully
            $url = $result['url'];
            $path = $result['path'];
        }
    }
}
```

### Direct Storage Usage

```php
use Illuminate\Support\Facades\Storage;

// Store in documents disk
Storage::disk('documents')->put('file.pdf', $content);

// Store in avatars disk
Storage::disk('avatars')->put('avatar.jpg', $content);

// Get secure URL for document
$url = Storage::disk('documents')->temporaryUrl('file.pdf', now()->addHour());
```

## Security Considerations

### Document Access Control
- Documents are stored with private visibility
- Access is controlled through signed URLs
- URLs expire after 1 hour by default

### File Validation
- All uploads are validated for file type and size
- Malware scanning is recommended for production
- File names are sanitized and made unique

### Backup Strategy
- Use separate storage for backups
- Consider cross-region replication for critical data
- Regular backup testing is recommended

## Performance Optimization

### CDN Integration
- Use CloudFront (AWS) or similar CDN for public assets
- Configure proper cache headers
- Consider image optimization for avatars

### Storage Classes
- Use appropriate storage classes (Standard, IA, Glacier)
- Implement lifecycle policies for cost optimization
- Monitor storage costs and usage

## Troubleshooting

### Common Issues

1. **Permission Errors**
   - Check IAM permissions for S3
   - Verify storage account permissions for Azure
   - Ensure service account has proper roles for GCS

2. **Connectivity Issues**
   - Use `php artisan storage:info --test` to diagnose
   - Check network connectivity and firewall rules
   - Verify endpoint URLs and regions

3. **File Not Found Errors**
   - Check if files exist using `Storage::exists()`
   - Verify correct disk configuration
   - Check file paths and naming

### Debug Mode

Enable debug logging for storage operations:

```env
LOG_LEVEL=debug
```

Check logs in `storage/logs/laravel.log` for detailed error information.

## Migration Between Storage Types

### From Local to Cloud

1. Update environment configuration
2. Test connectivity with new storage
3. Migrate existing files using custom command
4. Update application configuration
5. Verify all functionality works

### Backup Before Migration

Always backup your data before changing storage configuration:

```bash
# Create backup of current storage
php artisan backup:run --only-files
```

## Monitoring and Maintenance

### Regular Tasks

1. **Cleanup temporary files**: Run daily
2. **Monitor storage usage**: Check monthly
3. **Test backup restoration**: Test quarterly
4. **Review access logs**: Monitor for security issues
5. **Update credentials**: Rotate keys regularly

### Automated Cleanup

Add to your scheduler in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Clean up temporary files daily
    $schedule->command('storage:cleanup-temp')->daily();
}
```