<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Revisions\Revision;
use Statamic\Support\Str;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Carbon\Carbon;

class RevisionsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'revisions';
    }

    protected function getDefaultDirectory(): string
    {
        return 'storage/revisions';
    }

    public function getItemKey($item): string
    {
        return $item->key();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $data = Yaml::parse($contents);
        
        return new Revision([
            'key' => $this->getKeyFromPath($path),
            'path' => $path,
            'data' => $data,
            'date' => isset($data['date']) ? Carbon::parse($data['date']) : null,
            'user' => $data['user'] ?? null,
            'message' => $data['message'] ?? null,
        ]);
    }

    protected function getFilenameFromKey(string $key): string
    {
        // Revisions are typically stored with timestamp and content type
        // e.g., collections/posts/2023-01-01-123456.yaml
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = [
            'date' => $item->date() ? $item->date()->toDateTimeString() : null,
            'user' => $item->user(),
            'message' => $item->message(),
            'attributes' => $item->attributes(),
        ];
        
        // Remove null values
        $data = array_filter($data, function ($value) {
            return $value !== null;
        });

        return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        return Str::beforeLast($relativePath, '.yaml');
    }
}