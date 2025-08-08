<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Globals\Variables;
use Statamic\Support\Str;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class GlobalVariablesStore extends BaseS3Store
{
    public function key(): string
    {
        return 'global-variables';
    }

    protected function getDefaultDirectory(): string
    {
        return 'content/globals';
    }

    public function getItemKey($item): string
    {
        return $item->globalSet()->handle() . '::' . $item->locale();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        $relative = $file->getRelativePathname();
        
        // Filter for files in subdirectories (not root globals files)
        return $file->getExtension() === 'yaml' && substr_count($relative, '/') > 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $pathParts = explode('/', $relativePath);
        
        $globalSetHandle = $pathParts[0];
        $filename = end($pathParts);
        $locale = pathinfo($filename, PATHINFO_FILENAME);
        
        $data = Yaml::parse($contents);
        $globalSet = \Statamic\Facades\GlobalSet::findByHandle($globalSetHandle);
        
        return (new Variables)
            ->globalSet($globalSet)
            ->locale($locale)
            ->data($data ?? []);
    }

    protected function getFilenameFromKey(string $key): string
    {
        [$globalSetHandle, $locale] = explode('::', $key, 2);
        
        return $globalSetHandle . '/' . $locale . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = $item->data();
        
        return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $pathParts = explode('/', $relativePath);
        
        $globalSetHandle = $pathParts[0];
        $filename = end($pathParts);
        $locale = pathinfo($filename, PATHINFO_FILENAME);
        
        return $globalSetHandle . '::' . $locale;
    }
}