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
use Statamic\S3Filesystem\Stores\BlueprintsStore;
use Statamic\S3Filesystem\Stores\FieldsetsStore;
use Statamic\S3Filesystem\Stores\FormsStore;
use Statamic\S3Filesystem\Stores\FormSubmissionsStore;
use Statamic\S3Filesystem\Stores\UsersStore;
use Statamic\S3Filesystem\Stores\UserGroupsStore;
use Statamic\S3Filesystem\Stores\UserRolesStore;
use Statamic\S3Filesystem\Stores\SitesStore;
use Statamic\S3Filesystem\Stores\TokensStore;
use Statamic\S3Filesystem\Stores\RevisionsStore;
use Statamic\S3Filesystem\Stores\CollectionTreesStore;
use Statamic\S3Filesystem\Stores\GlobalVariablesStore;
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

        if (config('statamic.s3-filesystem.blueprints.driver') === 's3') {
            $this->app->singleton('stache.stores.blueprints', function ($app) {
                return new BlueprintsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.blueprints.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.fieldsets.driver') === 's3') {
            $this->app->singleton('stache.stores.fieldsets', function ($app) {
                return new FieldsetsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.fieldsets.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.forms.driver') === 's3') {
            $this->app->singleton('stache.stores.forms', function ($app) {
                return new FormsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.forms.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.form_submissions.driver') === 's3') {
            $this->app->singleton('stache.stores.form-submissions', function ($app) {
                return new FormSubmissionsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.form-submissions.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.users.driver') === 's3') {
            $this->app->singleton('stache.stores.users', function ($app) {
                return new UsersStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.users.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.user_groups.driver') === 's3') {
            $this->app->singleton('stache.stores.user-groups', function ($app) {
                return new UserGroupsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.user-groups.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.user_roles.driver') === 's3') {
            $this->app->singleton('stache.stores.user-roles', function ($app) {
                return new UserRolesStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.user-roles.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.sites.driver') === 's3') {
            $this->app->singleton('stache.stores.sites', function ($app) {
                return new SitesStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.sites.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.tokens.driver') === 's3') {
            $this->app->singleton('stache.stores.tokens', function ($app) {
                return new TokensStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.tokens.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.revisions.driver') === 's3') {
            $this->app->singleton('stache.stores.revisions', function ($app) {
                return new RevisionsStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.revisions.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.collection_trees.driver') === 's3') {
            $this->app->singleton('stache.stores.collection-trees', function ($app) {
                return new CollectionTreesStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.collection-trees.key')
                );
            });
        }

        if (config('statamic.s3-filesystem.global_variables.driver') === 's3') {
            $this->app->singleton('stache.stores.global-variables', function ($app) {
                return new GlobalVariablesStore(
                    $app->make(S3ContentFilesystem::class),
                    config('statamic.stache.stores.global-variables.key')
                );
            });
        }
    }
}