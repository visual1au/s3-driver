# Statamic S3 Filesystem Driver

A Laravel/Statamic addon that enables storing Statamic's flat-file content (YAML, Markdown, JSON files) in AWS S3 instead of local disk storage. Perfect for Laravel Vapor deployments where local disk storage is not persistent.

This addon maintains Statamic's native flat-file structure while seamlessly integrating with AWS S3 using Laravel's filesystem abstraction.

## Features

- **Pure Filesystem Approach**: No database required - maintains Statamic's flat-file philosophy
- **S3 Integration**: Uses Laravel's native S3 filesystem driver via Flysystem
- **Complete Content Support**: Handles collections, entries, globals, taxonomies, terms, assets, and navigation
- **Vapor Compatible**: Perfect for Laravel Vapor and other ephemeral filesystem environments  
- **Performance Optimized**: Built-in caching layer for improved S3 read performance
- **Seamless Integration**: Drop-in replacement for Statamic's file-based stores
- **Configurable**: Flexible configuration for different content types and caching strategies

## Installation

### 1. Install the Package

```bash
composer require statamic/s3-filesystem-driver
```

### 2. Configure AWS S3

First, ensure your Laravel application is configured to use S3. Add your S3 configuration to `config/filesystems.php`:

```php
'disks' => [
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
    ],
],
```

Add your S3 credentials to your `.env` file:

```env
AWS_ACCESS_KEY_ID=your-access-key-id
AWS_SECRET_ACCESS_KEY=your-secret-access-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-statamic-content-bucket
```

### 3. Publish Configuration

```bash
php artisan vendor:publish --tag=statamic-s3-filesystem-config
```

This creates `config/statamic/s3-filesystem.php` where you can configure which content types should use S3 storage.

### 4. Configure Content Storage

Edit `config/statamic/s3-filesystem.php` to enable S3 storage for the content types you want:

```php
return [
    'disk' => env('STATAMIC_S3_DISK', 's3'),
    'prefix' => env('STATAMIC_S3_PREFIX', 'content'),

    // Enable S3 storage for different content types
    'collections' => ['driver' => 's3'],
    'entries' => ['driver' => 's3'],
    'globals' => ['driver' => 's3'],
    'taxonomies' => ['driver' => 's3'],
    'terms' => ['driver' => 's3'],
    'assets' => ['driver' => 's3'],
    'navigations' => ['driver' => 's3'],
    'navigation_trees' => ['driver' => 's3'],

    // Configure caching for performance
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'store' => null, // Use default cache store
    ],
];
```

### 5. Environment Variables (Optional)

You can also configure the addon using environment variables:

```env
STATAMIC_S3_DISK=s3
STATAMIC_S3_PREFIX=content
STATAMIC_S3_COLLECTIONS_DRIVER=s3
STATAMIC_S3_ENTRIES_DRIVER=s3
STATAMIC_S3_GLOBALS_DRIVER=s3
STATAMIC_S3_TAXONOMIES_DRIVER=s3
STATAMIC_S3_TERMS_DRIVER=s3
STATAMIC_S3_ASSETS_DRIVER=s3
STATAMIC_S3_NAVIGATIONS_DRIVER=s3
STATAMIC_S3_NAVIGATION_TREES_DRIVER=s3
STATAMIC_S3_CACHE_ENABLED=true
STATAMIC_S3_CACHE_TTL=3600
```

## Usage

Once installed and configured, the addon works transparently. Statamic will automatically read from and write to S3 instead of local files for the configured content types.

### Content Structure in S3

The addon maintains the same directory structure in S3 that Statamic uses locally:

```
your-s3-bucket/
└── content/                    (configurable prefix)
    ├── assets/
    │   ├── assets.yaml
    │   └── container-name.yaml
    ├── collections/
    │   ├── pages.yaml
    │   ├── posts.yaml
    │   └── pages/
    │       └── home.md
    ├── globals/
    │   └── settings.yaml
    ├── navigation/
    │   └── main.yaml
    ├── taxonomies/
    │   ├── categories.yaml
    │   └── categories/
    │       └── technology.md
    └── trees/
        └── navigation/
            └── main.yaml
```

### Migrating Existing Content

To migrate existing local content to S3, you can:

1. **Upload manually**: Copy your existing `content/` directory to your S3 bucket
2. **Use AWS CLI**:
   ```bash
   aws s3 sync ./content s3://your-bucket/content --delete
   ```
3. **Use Laravel commands**:
   ```php
   // Custom artisan command to sync content
   Storage::disk('s3')->put('content/collections/posts.yaml', 
       file_get_contents(resource_path('content/collections/posts.yaml'))
   );
   ```

### Performance Considerations

The addon includes several performance optimizations:

- **Caching**: Configurable caching of file contents and directory listings
- **Batch Operations**: Efficient handling of multiple file operations
- **Lazy Loading**: Files are only loaded when needed
- **Change Detection**: Efficient change detection using S3 object metadata

For best performance in production:

1. Enable caching with a reasonable TTL
2. Use a fast cache store (Redis recommended)
3. Configure your S3 bucket in the same region as your application
4. Consider using CloudFront for additional caching if needed

## Configuration Options

### Disk Configuration

| Option | Description | Default |
|--------|-------------|---------|
| `disk` | Laravel filesystem disk to use | `s3` |
| `prefix` | S3 path prefix for content files | `content` |

### Content Type Configuration

Each content type can be configured individually:

```php
'collections' => [
    'driver' => 's3', // Use 'file' to keep local storage
],
```

### Caching Configuration

| Option | Description | Default |
|--------|-------------|---------|
| `cache.enabled` | Enable/disable caching | `true` |
| `cache.store` | Cache store to use | `null` (default) |
| `cache.ttl` | Cache time-to-live in seconds | `3600` |
| `cache.key_prefix` | Cache key prefix | `statamic_s3` |

## Laravel Vapor Deployment

This addon is specifically designed for Laravel Vapor. Here's a complete setup:

### 1. Vapor Configuration (`vapor.yml`)

```yaml
id: your-project-id
name: your-project-name
environments:
    production:
        memory: 1024
        cli-memory: 512
        runtime: 'php-8.2'
        build:
            - 'composer install --no-dev'
            - 'npm ci && npm run build'
            - 'php artisan config:cache'
            - 'php artisan route:cache'
            - 'php artisan view:cache'
        deploy:
            - 'php artisan statamic:stache:warm'
        variables:
            STATAMIC_S3_DISK: s3
            STATAMIC_S3_COLLECTIONS_DRIVER: s3
            STATAMIC_S3_ENTRIES_DRIVER: s3
            STATAMIC_S3_GLOBALS_DRIVER: s3
```

### 2. Environment Configuration

Ensure your production environment has:

```env
STATAMIC_S3_DISK=s3
STATAMIC_S3_PREFIX=content
# Enable S3 for all content types
STATAMIC_S3_COLLECTIONS_DRIVER=s3
STATAMIC_S3_ENTRIES_DRIVER=s3
STATAMIC_S3_GLOBALS_DRIVER=s3
STATAMIC_S3_TAXONOMIES_DRIVER=s3
STATAMIC_S3_TERMS_DRIVER=s3
STATAMIC_S3_ASSETS_DRIVER=s3
STATAMIC_S3_NAVIGATIONS_DRIVER=s3
STATAMIC_S3_NAVIGATION_TREES_DRIVER=s3
```

### 3. Pre-deployment Content Upload

Before your first deployment, upload your content to S3:

```bash
aws s3 sync ./content s3://your-vapor-bucket/content --delete
```

## Troubleshooting

### Common Issues

1. **Permission Errors**: Ensure your AWS credentials have read/write access to the S3 bucket
2. **Caching Issues**: Clear application cache if content changes aren't reflected
3. **Path Issues**: Verify the S3 prefix configuration matches your bucket structure

### Debug Mode

Enable debug logging to troubleshoot issues:

```php
// In a service provider or controller
Log::debug('S3 Filesystem Debug', [
    'disk' => config('statamic.s3-filesystem.disk'),
    'prefix' => config('statamic.s3-filesystem.prefix'),
    'cache_enabled' => config('statamic.s3-filesystem.cache.enabled'),
]);
```

### Performance Monitoring

Monitor S3 API calls and costs:

```php
// Add to your monitoring dashboard
$s3Calls = CloudWatch::getMetric('S3', 'NumberOfObjects');
$s3Costs = CloudWatch::getMetric('S3', 'BucketSizeBytes');
```

## Requirements

- PHP 8.1+
- Laravel 10.0+ 
- Statamic 4.0+ or 5.0+
- AWS S3 bucket with appropriate permissions
- `league/flysystem-aws-s3-v3` ^3.0

## License

MIT License - see LICENSE file for details.

## Support

For issues and questions:

1. Check the troubleshooting section above
2. Review your S3 and Laravel filesystem configuration
3. Open an issue on GitHub with detailed information about your setup

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Submit a pull request

## Changelog

### 1.0.0
- Initial release
- Support for all Statamic content types
- S3 filesystem integration
- Performance caching
- Laravel Vapor compatibility