<?php

namespace Statamic\S3Filesystem\Stores;

use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Carbon\Carbon;

class TokensStore extends BaseS3Store
{
    public function key(): string
    {
        return 'tokens';
    }

    protected function getDefaultDirectory(): string
    {
        return 'storage/tokens';
    }

    public function getItemKey($item): string
    {
        return $item['token'];
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $token = pathinfo($path, PATHINFO_FILENAME);
        $data = Yaml::parse($contents);
        
        return array_merge($data, ['token' => $token]);
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = $item;
        unset($data['token']); // Remove token from data as it's stored in filename
        
        return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}