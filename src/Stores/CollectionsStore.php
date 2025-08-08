<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Collection;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class CollectionsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'collections';
    }

    protected function getDefaultDirectory(): string
    {
        return 'collections';
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

        return Collection::make($handle)
            ->title(Arr::get($data, 'title'))
            ->routes(Arr::get($data, 'route'))
            ->mount(Arr::get($data, 'mount'))
            ->dated(Arr::get($data, 'date'))
            ->ampable(Arr::get($data, 'amp'))
            ->sortField(Arr::get($data, 'sort_by'))
            ->sortDirection(Arr::get($data, 'sort_dir'))
            ->structure(Arr::get($data, 'structure'))
            ->taxonomies(Arr::get($data, 'taxonomies'))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->revisions(Arr::get($data, 'revisions'))
            ->defaultPublishState(Arr::get($data, 'default_status', true))
            ->originExcludes(Arr::get($data, 'origin_exclude', []))
            ->sites(Arr::get($data, 'sites'))
            ->template(Arr::get($data, 'template'))
            ->layout(Arr::get($data, 'layout'))
            ->cascade(Arr::get($data, 'inject', []))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->pastDateBehavior(Arr::get($data, 'past_date_behavior'))
            ->futureDateBehavior(Arr::get($data, 'future_date_behavior'));
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = [
            'title' => $item->title(),
            'route' => $item->route(),
            'mount' => $item->mount(),
            'date' => $item->dated(),
            'amp' => $item->ampable(),
            'sort_by' => $item->sortField(),
            'sort_dir' => $item->sortDirection(),
            'structure' => $item->structure()?->handle(),
            'taxonomies' => $item->taxonomies()?->map->handle()->all(),
            'search_index' => $item->searchIndex(),
            'revisions' => $item->revisionsEnabled(),
            'default_status' => $item->defaultPublishState(),
            'origin_exclude' => $item->originExcludes(),
            'sites' => $item->sites()?->all(),
            'template' => $item->template(),
            'layout' => $item->layout(),
            'inject' => $item->cascade(),
            'past_date_behavior' => $item->pastDateBehavior(),
            'future_date_behavior' => $item->futureDateBehavior(),
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