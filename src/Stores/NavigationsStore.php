<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Nav;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class NavigationsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'navigations';
    }

    protected function getDefaultDirectory(): string
    {
        return 'navigation';
    }

    public function getItemKey($item): string
    {
        return $item->handle();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        $relative = $file->getRelativePathname();
        
        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = Yaml::parse($contents);

        return Nav::make($handle)
            ->title(Arr::get($data, 'title'))
            ->maxDepth(Arr::get($data, 'max_depth'))
            ->expectsRoot(Arr::get($data, 'expects_root'))
            ->collections(Arr::get($data, 'collections', []))
            ->sites(Arr::get($data, 'sites'));
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = [
            'title' => $item->title(),
            'max_depth' => $item->maxDepth(),
            'expects_root' => $item->expectsRoot(),
            'collections' => $item->collections()->all(),
            'sites' => $item->sites()?->all(),
        ];

        // Remove null values
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });

        return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}