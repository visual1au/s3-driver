<?php

namespace Statamic\S3Filesystem\Filesystem;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Statamic\Support\Str;

class S3ContentFilesystem
{
    protected FilesystemAdapter $disk;
    protected string $prefix;
    protected bool $cacheEnabled;
    protected ?string $cacheStore;
    protected int $cacheTtl;
    protected string $cachePrefix;

    public function __construct()
    {
        $this->disk = Storage::disk(config('statamic.s3-filesystem.disk'));
        $this->prefix = rtrim(config('statamic.s3-filesystem.prefix', ''), '/');
        $this->cacheEnabled = config('statamic.s3-filesystem.cache.enabled', true);
        $this->cacheStore = config('statamic.s3-filesystem.cache.store');
        $this->cacheTtl = config('statamic.s3-filesystem.cache.ttl', 3600);
        $this->cachePrefix = config('statamic.s3-filesystem.cache.key_prefix', 'statamic_s3');
    }

    public function exists(string $path): bool
    {
        return $this->disk->exists($this->prefixPath($path));
    }

    public function get(string $path): ?string
    {
        if (!$this->exists($path)) {
            return null;
        }

        if ($this->cacheEnabled) {
            $cacheKey = $this->getCacheKey('content', $path);
            return $this->cache()->remember($cacheKey, $this->cacheTtl, function () use ($path) {
                return $this->disk->get($this->prefixPath($path));
            });
        }

        return $this->disk->get($this->prefixPath($path));
    }

    public function put(string $path, string $contents): bool
    {
        $prefixedPath = $this->prefixPath($path);
        $result = $this->disk->put($prefixedPath, $contents);
        
        if ($result && $this->cacheEnabled) {
            $this->invalidateCache($path);
            $this->invalidateDirectoryCache(dirname($path));
        }
        
        return $result;
    }

    public function delete(string $path): bool
    {
        $prefixedPath = $this->prefixPath($path);
        $result = $this->disk->delete($prefixedPath);
        
        if ($result && $this->cacheEnabled) {
            $this->invalidateCache($path);
            $this->invalidateDirectoryCache(dirname($path));
        }
        
        return $result;
    }

    public function copy(string $from, string $to): bool
    {
        $result = $this->disk->copy($this->prefixPath($from), $this->prefixPath($to));
        
        if ($result && $this->cacheEnabled) {
            $this->invalidateCache($to);
            $this->invalidateDirectoryCache(dirname($to));
        }
        
        return $result;
    }

    public function move(string $from, string $to): bool
    {
        $result = $this->disk->move($this->prefixPath($from), $this->prefixPath($to));
        
        if ($result && $this->cacheEnabled) {
            $this->invalidateCache($from);
            $this->invalidateCache($to);
            $this->invalidateDirectoryCache(dirname($from));
            $this->invalidateDirectoryCache(dirname($to));
        }
        
        return $result;
    }

    public function lastModified(string $path): int
    {
        return $this->disk->lastModified($this->prefixPath($path));
    }

    public function size(string $path): int
    {
        return $this->disk->size($this->prefixPath($path));
    }

    public function files(string $directory = '', bool $recursive = false): Collection
    {
        if ($this->cacheEnabled) {
            $cacheKey = $this->getCacheKey('files', $directory, $recursive ? 'recursive' : 'flat');
            return $this->cache()->remember($cacheKey, $this->cacheTtl, function () use ($directory, $recursive) {
                return $this->getFilesFromDisk($directory, $recursive);
            });
        }

        return $this->getFilesFromDisk($directory, $recursive);
    }

    public function directories(string $directory = ''): Collection
    {
        if ($this->cacheEnabled) {
            $cacheKey = $this->getCacheKey('directories', $directory);
            return $this->cache()->remember($cacheKey, $this->cacheTtl, function () use ($directory) {
                return $this->getDirectoriesFromDisk($directory);
            });
        }

        return $this->getDirectoriesFromDisk($directory);
    }

    public function allFiles(string $directory = ''): Collection
    {
        return $this->files($directory, true);
    }

    public function makeDirectory(string $path): bool
    {
        $dummyFile = rtrim($path, '/') . '/.gitkeep';
        $result = $this->put($dummyFile, '');
        
        if ($result && $this->cacheEnabled) {
            $this->invalidateDirectoryCache($path);
            $this->invalidateDirectoryCache(dirname($path));
        }
        
        return $result;
    }

    public function deleteDirectory(string $directory): bool
    {
        $prefixedPath = $this->prefixPath($directory);
        $result = $this->disk->deleteDirectory($prefixedPath);
        
        if ($result && $this->cacheEnabled) {
            $this->invalidateDirectoryCache($directory);
            $this->invalidateDirectoryCache(dirname($directory));
        }
        
        return $result;
    }

    public function url(string $path): string
    {
        return $this->disk->url($this->prefixPath($path));
    }

    protected function prefixPath(string $path): string
    {
        $path = ltrim($path, '/');
        return $this->prefix ? "{$this->prefix}/{$path}" : $path;
    }

    protected function unprefixPath(string $path): string
    {
        if ($this->prefix && Str::startsWith($path, $this->prefix . '/')) {
            return substr($path, strlen($this->prefix) + 1);
        }
        return $path;
    }

    protected function getFilesFromDisk(string $directory, bool $recursive): Collection
    {
        $prefixedDirectory = $this->prefixPath($directory);
        $method = $recursive ? 'allFiles' : 'files';
        
        $files = collect($this->disk->$method($prefixedDirectory))
            ->map(fn($file) => $this->unprefixPath($file))
            ->filter(fn($file) => !Str::endsWith($file, '/.gitkeep'));
            
        return $files;
    }

    protected function getDirectoriesFromDisk(string $directory): Collection
    {
        $prefixedDirectory = $this->prefixPath($directory);
        
        return collect($this->disk->directories($prefixedDirectory))
            ->map(fn($dir) => $this->unprefixPath($dir));
    }

    protected function getCacheKey(string $type, string $path, ?string $suffix = null): string
    {
        $key = "{$this->cachePrefix}:{$type}:" . md5($path);
        return $suffix ? "{$key}:{$suffix}" : $key;
    }

    protected function cache()
    {
        return $this->cacheStore ? Cache::store($this->cacheStore) : Cache::store();
    }

    protected function invalidateCache(string $path): void
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $cache = $this->cache();
        $cache->forget($this->getCacheKey('content', $path));
    }

    protected function invalidateDirectoryCache(string $directory): void
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $cache = $this->cache();
        $cache->forget($this->getCacheKey('files', $directory, 'flat'));
        $cache->forget($this->getCacheKey('files', $directory, 'recursive'));
        $cache->forget($this->getCacheKey('directories', $directory));
        
        // Also invalidate parent directory listings
        if ($directory !== '.' && $directory !== '') {
            $this->invalidateDirectoryCache(dirname($directory));
        }
    }
}