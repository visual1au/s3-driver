<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Stache\Stores\AggregateStore;
use Statamic\Facades\Taxonomy;
use Statamic\S3Filesystem\Filesystem\S3ContentFilesystem;

class TermsStore extends AggregateStore
{
    protected S3ContentFilesystem $s3Filesystem;

    public function __construct(S3ContentFilesystem $s3Filesystem, $storeKey = null)
    {
        $this->s3Filesystem = $s3Filesystem;
        parent::__construct();
    }

    public function key(): string
    {
        return 'terms';
    }

    public function getChildStores(): array
    {
        return Taxonomy::handles()->mapWithKeys(function ($handle) {
            $store = new TaxonomyTermsStore($this->s3Filesystem);
            $store->directory("taxonomies/{$handle}");
            $store->taxonomyHandle($handle);
            
            return [$handle => $store];
        })->all();
    }
}