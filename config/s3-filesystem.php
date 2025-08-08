<?php

return [
    /*
    |--------------------------------------------------------------------------
    | S3 Filesystem Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration determines how Statamic content will be stored
    | in AWS S3. The filesystem configuration should match a disk
    | configured in your filesystems.php config file.
    |
    */

    'disk' => env('STATAMIC_S3_DISK', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Content Path Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be prepended to all content paths in S3.
    | Useful for organizing content in a specific folder structure.
    |
    */

    'prefix' => env('STATAMIC_S3_PREFIX', 'content'),

    /*
    |--------------------------------------------------------------------------
    | Content Types Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which content types should use S3 storage.
    | Set driver to 's3' to enable S3 storage for that content type.
    |
    */

    'collections' => [
        'driver' => env('STATAMIC_S3_COLLECTIONS_DRIVER', 'file'),
    ],

    'entries' => [
        'driver' => env('STATAMIC_S3_ENTRIES_DRIVER', 'file'),
    ],

    'globals' => [
        'driver' => env('STATAMIC_S3_GLOBALS_DRIVER', 'file'),
    ],

    'taxonomies' => [
        'driver' => env('STATAMIC_S3_TAXONOMIES_DRIVER', 'file'),
    ],

    'terms' => [
        'driver' => env('STATAMIC_S3_TERMS_DRIVER', 'file'),
    ],

    'assets' => [
        'driver' => env('STATAMIC_S3_ASSETS_DRIVER', 'file'),
        'containers' => [
            'driver' => env('STATAMIC_S3_ASSET_CONTAINERS_DRIVER', 'file'),
        ],
    ],

    'navigations' => [
        'driver' => env('STATAMIC_S3_NAVIGATIONS_DRIVER', 'file'),
    ],

    'navigation_trees' => [
        'driver' => env('STATAMIC_S3_NAVIGATION_TREES_DRIVER', 'file'),
    ],

    'blueprints' => [
        'driver' => env('STATAMIC_S3_BLUEPRINTS_DRIVER', 'file'),
    ],

    'fieldsets' => [
        'driver' => env('STATAMIC_S3_FIELDSETS_DRIVER', 'file'),
    ],

    'forms' => [
        'driver' => env('STATAMIC_S3_FORMS_DRIVER', 'file'),
    ],

    'form_submissions' => [
        'driver' => env('STATAMIC_S3_FORM_SUBMISSIONS_DRIVER', 'file'),
    ],

    'users' => [
        'driver' => env('STATAMIC_S3_USERS_DRIVER', 'file'),
    ],

    'user_groups' => [
        'driver' => env('STATAMIC_S3_USER_GROUPS_DRIVER', 'file'),
    ],

    'user_roles' => [
        'driver' => env('STATAMIC_S3_USER_ROLES_DRIVER', 'file'),
    ],

    'sites' => [
        'driver' => env('STATAMIC_S3_SITES_DRIVER', 'file'),
    ],

    'tokens' => [
        'driver' => env('STATAMIC_S3_TOKENS_DRIVER', 'file'),
    ],

    'revisions' => [
        'driver' => env('STATAMIC_S3_REVISIONS_DRIVER', 'file'),
    ],

    'collection_trees' => [
        'driver' => env('STATAMIC_S3_COLLECTION_TREES_DRIVER', 'file'),
    ],

    'global_variables' => [
        'driver' => env('STATAMIC_S3_GLOBAL_VARIABLES_DRIVER', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Enable caching to improve performance when reading files from S3.
    | Cache will be invalidated when files are modified.
    |
    */

    'cache' => [
        'enabled' => env('STATAMIC_S3_CACHE_ENABLED', true),
        'store' => env('STATAMIC_S3_CACHE_STORE', null),
        'ttl' => env('STATAMIC_S3_CACHE_TTL', 3600), // 1 hour in seconds
        'key_prefix' => env('STATAMIC_S3_CACHE_PREFIX', 'statamic_s3'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Extension Mapping
    |--------------------------------------------------------------------------
    |
    | Map content types to their expected file extensions.
    |
    */

    'extensions' => [
        'collections' => 'yaml',
        'entries' => 'md',
        'globals' => 'yaml',
        'taxonomies' => 'yaml',
        'terms' => 'md',
        'assets' => 'yaml',
        'navigations' => 'yaml',
        'blueprints' => 'yaml',
        'fieldsets' => 'yaml',
        'forms' => 'yaml',
        'form_submissions' => 'yaml',
        'users' => 'yaml',
        'user_groups' => 'yaml',
        'user_roles' => 'yaml',
        'sites' => 'yaml',
        'tokens' => 'yaml',
        'revisions' => 'yaml',
        'collection_trees' => 'yaml',
        'global_variables' => 'yaml',
    ],
];