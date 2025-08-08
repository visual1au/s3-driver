<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\GlobalSet;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class GlobalsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'globals';
    }

    protected function getDefaultDirectory(): string
    {
        return 'globals';
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

        return GlobalSet::make($handle)
            ->title(Arr::get($data, 'title', $handle));
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = [
            'title' => $item->title(),
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