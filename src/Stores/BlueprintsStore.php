<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Fields\Blueprint;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class BlueprintsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'blueprints';
    }

    protected function getDefaultDirectory(): string
    {
        return 'resources/blueprints';
    }

    public function getItemKey($item): string
    {
        return $item->namespace() . '.' . $item->handle();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relativePath = str_replace($this->directory() . '/', '', $path);
        $pathParts = explode('/', $relativePath);
        
        $filename = array_pop($pathParts);
        $handle = pathinfo($filename, PATHINFO_FILENAME);
        $namespace = implode('.', $pathParts) ?: 'default';
        
        $data = Yaml::parse($contents);

        return Blueprint::make($handle)
            ->setNamespace($namespace)
            ->setContents($data);
    }

    protected function getFilenameFromKey(string $key): string
    {
        $parts = explode('.', $key);
        $handle = array_pop($parts);
        $namespace = implode('/', $parts);
        
        return ($namespace === 'default' ? '' : $namespace . '/') . $handle . '.yaml';
    }

    protected function getItemContents($item): string
    {
        return Yaml::dump($item->contents(), 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        $relativePath = str_replace($this->directory() . '/', '', $path);
        $pathParts = explode('/', $relativePath);
        
        $filename = array_pop($pathParts);
        $handle = pathinfo($filename, PATHINFO_FILENAME);
        $namespace = implode('.', $pathParts) ?: 'default';
        
        return $namespace . '.' . $handle;
    }
}