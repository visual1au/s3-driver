<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Nav;
use Statamic\Structures\NavTree;
use Statamic\Support\Str;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class NavigationTreesStore extends BaseS3Store
{
    public function key(): string
    {
        return 'navigation-trees';
    }

    protected function getDefaultDirectory(): string
    {
        return 'trees/navigation';
    }

    public function getItemKey($item): string
    {
        return $item->handle() . '::' . $item->locale();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);
        
        // Extract navigation handle and locale from filename
        // Expecting format like: navigation_handle.locale.yaml or navigation_handle.yaml
        $parts = explode('.', $filename);
        $handle = $parts[0];
        $locale = isset($parts[1]) ? $parts[1] : config('app.locale');
        
        $data = Yaml::parse($contents);
        $nav = Nav::findByHandle($handle);
        
        return (new NavTree)
            ->nav($nav)
            ->locale($locale)
            ->tree($data['tree'] ?? []);
    }

    protected function getFilenameFromKey(string $key): string
    {
        [$handle, $locale] = explode('::', $key, 2);
        
        if ($locale === config('app.locale')) {
            return $handle . '.yaml';
        }
        
        return $handle . '.' . $locale . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = [
            'tree' => $item->tree(),
        ];

        return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $filename = pathinfo($relativePath, PATHINFO_FILENAME);
        
        $parts = explode('.', $filename);
        $handle = $parts[0];
        $locale = isset($parts[1]) ? $parts[1] : config('app.locale');
        
        return $handle . '::' . $locale;
    }
}