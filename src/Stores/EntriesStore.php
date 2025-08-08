<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Stache\Stores\AggregateStore;
use Statamic\Facades\Collection;
use Statamic\S3Filesystem\Filesystem\S3ContentFilesystem;

class EntriesStore extends AggregateStore
{
    protected S3ContentFilesystem $s3Filesystem;

    public function __construct(S3ContentFilesystem $s3Filesystem, $storeKey = null)
    {
        $this->s3Filesystem = $s3Filesystem;
        parent::__construct();
    }

    public function key(): string
    {
        return 'entries';
    }

    public function getChildStores(): array
    {
        return Collection::handles()->mapWithKeys(function ($handle) {
            $store = new CollectionEntriesStore($this->s3Filesystem);
            $store->directory("collections/{$handle}");
            $store->collectionHandle($handle);
            
            return [$handle => $store];
        })->all();
    }
}