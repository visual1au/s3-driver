<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Stache\Stores\BasicStore;
use Statamic\S3Filesystem\Filesystem\S3ContentFilesystem;
use Statamic\Support\Str;
use SplFileInfo;
use Illuminate\Support\Collection;

abstract class BaseS3Store extends BasicStore
{
    protected S3ContentFilesystem $s3Filesystem;

    public function __construct(S3ContentFilesystem $s3Filesystem, $storeKey = null)
    {
        $this->s3Filesystem = $s3Filesystem;
        
        if ($storeKey) {
            $this->key = $storeKey;
        }
        
        parent::__construct();
    }

    public function directory(string $directory = null): string
    {
        if ($directory !== null) {
            $this->directory = $directory;
            return $this;
        }
        
        return $this->directory ?? $this->getDefaultDirectory();
    }

    abstract protected function getDefaultDirectory(): string;

    public function paths(): Collection
    {
        $directory = $this->directory();
        
        return $this->s3Filesystem->allFiles($directory)
            ->filter(function ($path) {
                return $this->getItemFilter($this->createSplFileInfoFromPath($path));
            })
            ->mapWithKeys(function ($path) {
                $relativePath = Str::after($path, $this->directory() . '/');
                $key = $this->getKeyFromPath($relativePath);
                return [$key => $path];
            });
    }

    public function getItem($key)
    {
        $path = $this->paths()->get($key);
        
        if (!$path || !$this->s3Filesystem->exists($path)) {
            return null;
        }
        
        $contents = $this->s3Filesystem->get($path);
        
        return $this->makeItemFromFile($path, $contents);
    }

    protected function writeItemToDisk($item): void
    {
        $path = $this->getItemPath($item);
        $contents = $this->getItemContents($item);
        
        $this->s3Filesystem->put($path, $contents);
    }

    protected function deleteItemFromDisk($item): void
    {
        $path = $this->getItemPath($item);
        
        $this->s3Filesystem->delete($path);
    }

    protected function getItemPath($item): string
    {
        $key = $this->getItemKey($item);
        $filename = $this->getFilenameFromKey($key);
        
        return $this->directory() . '/' . $filename;
    }

    abstract protected function getFilenameFromKey(string $key): string;

    abstract protected function getItemContents($item): string;

    abstract protected function getKeyFromPath(string $path): string;

    protected function createSplFileInfoFromPath(string $path): SplFileInfo
    {
        $filename = basename($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        return new class($path, $filename, $extension) extends SplFileInfo {
            private string $extension;
            private string $relativePath;
            
            public function __construct(string $path, string $filename, string $extension)
            {
                parent::__construct($filename);
                $this->extension = $extension;
                $this->relativePath = $path;
            }
            
            public function getExtension(): string
            {
                return $this->extension;
            }
            
            public function getRelativePathname(): string
            {
                return $this->relativePath;
            }
        };
    }

    public function getModified($item): int
    {
        $path = $this->getItemPath($item);
        
        if (!$this->s3Filesystem->exists($path)) {
            return 0;
        }
        
        return $this->s3Filesystem->lastModified($path);
    }

    public function clear(): void
    {
        // Clear cache if needed
        parent::clear();
    }

    protected function handleFileChanges(): void
    {
        $paths = $this->paths();
        
        $current = collect($this->index()->keys())->flip();
        $files = $paths->keys();
        
        $deleted = $current->diffKeys($files->flip());
        $added = $files->diff($current->keys());
        $modified = $files->intersect($current->keys())
            ->reject(function ($key) use ($paths) {
                $path = $paths->get($key);
                $lastModified = $this->s3Filesystem->lastModified($path);
                return $this->index()->getItemData($key)['modified'] === $lastModified;
            });

        $this->handleDeletedItems($deleted->keys());
        $this->handleModifiedItems($added->merge($modified));
    }
}