<?php

namespace Statamic\S3Filesystem;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Statamic\S3Filesystem\Stores\CollectionsStore;
use Statamic\S3Filesystem\Stores\EntriesStore;
use Statamic\S3Filesystem\Stores\GlobalsStore;
use Statamic\S3Filesystem\Stores\TaxonomiesStore;
use Statamic\S3Filesystem\Stores\TermsStore;
use Statamic\S3Filesystem\Stores\AssetsStore;
use Statamic\S3Filesystem\Stores\AssetContainersStore;
use Statamic\S3Filesystem\Stores\NavigationsStore;
use Statamic\S3Filesystem\Stores\NavigationTreesStore;
use Statamic\S3Filesystem\Filesystem\S3ContentFilesystem;

class ServiceProvider extends AddonServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/s3-filesystem.php', 'statamic.s3-filesystem');
        
        $this->registerS3Stores();
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/s3-filesystem.php' => config_path('statamic/s3-filesystem.php'),
        ], 'statamic-s3-filesystem-config');
    }

    private function registerS3Stores()
    {
        if (config('statamic.s3-filesystem.collections.driver') === 's3') {
            $this->app->singleton('stache.stores.collections', function ($app) {
                return new CollectionsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.collections.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.entries.driver') === 's3') {
            $this->app->singleton('stache.stores.entries', function ($app) {
                return new EntriesStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.entries.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.globals.driver') === 's3') {
            $this->app->singleton('stache.stores.globals', function ($app) {
                return new GlobalsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.globals.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.taxonomies.driver') === 's3') {
            $this->app->singleton('stache.stores.taxonomies', function ($app) {
                return new TaxonomiesStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.taxonomies.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.terms.driver') === 's3') {
            $this->app->singleton('stache.stores.terms', function ($app) {
                return new TermsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.terms.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.assets.driver') === 's3') {
            $this->app->singleton('stache.stores.assets', function ($app) {
                return new AssetsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.assets.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.assets.containers.driver') === 's3') {
            $this->app->singleton('stache.stores.asset-containers', function ($app) {
                return new AssetContainersStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.asset-containers.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.navigations.driver') === 's3') {
            $this->app->singleton('stache.stores.navigations', function ($app) {
                return new NavigationsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.navigations.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.navigation_trees.driver') === 's3') {
            $this->app->singleton('stache.stores.navigation-trees', function ($app) {
                return new NavigationTreesStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.navigation-trees.key')
                );
            });
        }
    }
}