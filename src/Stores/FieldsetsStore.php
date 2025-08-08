<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Fields\Fieldset;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class FieldsetsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'fieldsets';
    }

    protected function getDefaultDirectory(): string
    {
        return 'resources/fieldsets';
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

        return Fieldset::make($handle)->setContents($data);
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        return Yaml::dump($item->contents(), 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}