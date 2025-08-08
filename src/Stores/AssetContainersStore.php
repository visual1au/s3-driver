<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\AssetContainer;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class AssetContainersStore extends BaseS3Store
{
    public function key(): string
    {
        return 'asset-containers';
    }

    protected function getDefaultDirectory(): string
    {
        return 'assets';
    }

    public function getItemKey($item): string
    {
        return $item->handle();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        $relative = $file->getRelativePathname();
        
        return $file->getExtension() === 'yaml' 
            && substr_count($relative, '/') === 0
            && $relative !== 'assets.yaml'; // Skip the main assets config file
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = Yaml::parse($contents);

        return AssetContainer::make($handle)
            ->disk(Arr::get($data, 'disk'))
            ->title(Arr::get($data, 'title'))
            ->allowUploads(Arr::get($data, 'allow_uploads'))
            ->allowDownloading(Arr::get($data, 'allow_downloading'))
            ->allowRenaming(Arr::get($data, 'allow_renaming'))
            ->allowMoving(Arr::get($data, 'allow_moving'))
            ->createFolders(Arr::get($data, 'create_folders'))
            ->searchIndex(Arr::get($data, 'search_index'));
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = [
            'disk' => $item->diskHandle(),
            'title' => $item->title(),
            'allow_uploads' => $item->allowUploads(),
            'allow_downloading' => $item->allowDownloading(),
            'allow_renaming' => $item->allowRenaming(),
            'allow_moving' => $item->allowMoving(),
            'create_folders' => $item->createsFolders(),
            'search_index' => $item->searchIndex(),
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