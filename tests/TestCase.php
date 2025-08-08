<?php

namespace Statamic\S3Filesystem\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\S3Filesystem\ServiceProvider;
use Statamic\Providers\StatamicServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.s3-test', [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_TEST_BUCKET', 'statamic-test-bucket'),
            'throw' => false,
        ]);

        $app['config']->set('statamic.s3-filesystem', [
            'disk' => 's3-test',
            'prefix' => 'test-content',
            'collections' => ['driver' => 's3'],
            'entries' => ['driver' => 's3'],
            'cache' => ['enabled' => false], // Disable cache for testing
        ]);
    }
}