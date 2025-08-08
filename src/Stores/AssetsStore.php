<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Asset;
use Statamic\Support\Str;
use SplFileInfo;

class AssetsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'assets';
    }

    protected function getDefaultDirectory(): string
    {
        return 'assets';
    }

    public function getItemKey($item): string
    {
        return $item->container()->handle() . '::' . $item->path();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $data = \Symfony\Component\Yaml\Yaml::parse($contents);
        
        // Extract container and asset path from the file path
        $pathParts = explode('/', $relativePath);
        $containerHandle = $pathParts[0];
        $assetPath = implode('/', array_slice($pathParts, 1));
        $assetPath = Str::beforeLast($assetPath, '.yaml');
        
        $container = \Statamic\Facades\AssetContainer::find($containerHandle);
        
        return Asset::make()
            ->container($container)
            ->path($assetPath)
            ->data($data ?? []);
    }

    protected function getFilenameFromKey(string $key): string
    {
        [$containerHandle, $assetPath] = explode('::', $key, 2);
        return $containerHandle . '/' . $assetPath . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = $item->data()->all();
        
        return \Symfony\Component\Yaml\Yaml::dump($data, 2, 2, \Symfony\Component\Yaml\Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $pathParts = explode('/', $relativePath);
        $containerHandle = $pathParts[0];
        $assetPath = implode('/', array_slice($pathParts, 1));
        $assetPath = Str::beforeLast($assetPath, '.yaml');
        
        return $containerHandle . '::' . $assetPath;
    }
}