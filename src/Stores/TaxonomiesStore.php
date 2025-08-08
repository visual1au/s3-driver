<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Taxonomy;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class TaxonomiesStore extends BaseS3Store
{
    public function key(): string
    {
        return 'taxonomies';
    }

    protected function getDefaultDirectory(): string
    {
        return 'taxonomies';
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

        return Taxonomy::make($handle)
            ->title(Arr::get($data, 'title'))
            ->sites(Arr::get($data, 'sites'))
            ->template(Arr::get($data, 'template'))
            ->layout(Arr::get($data, 'layout'))
            ->cascade(Arr::get($data, 'inject', []))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->revisions(Arr::get($data, 'revisions'))
            ->defaultPublishState(Arr::get($data, 'default_status', true))
            ->originExcludes(Arr::get($data, 'origin_exclude', []));
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = [
            'title' => $item->title(),
            'sites' => $item->sites()?->all(),
            'template' => $item->template(),
            'layout' => $item->layout(),
            'inject' => $item->cascade(),
            'search_index' => $item->searchIndex(),
            'revisions' => $item->revisionsEnabled(),
            'default_status' => $item->defaultPublishState(),
            'origin_exclude' => $item->originExcludes(),
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