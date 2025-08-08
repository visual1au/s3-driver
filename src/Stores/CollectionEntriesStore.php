<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Entry;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use SplFileInfo;

class CollectionEntriesStore extends BaseS3Store
{
    protected string $collectionHandle;

    public function key(): string
    {
        return "collection-entries-{$this->collectionHandle}";
    }

    protected function getDefaultDirectory(): string
    {
        return "collections/{$this->collectionHandle}";
    }

    public function collectionHandle(string $handle = null): string
    {
        if ($handle !== null) {
            $this->collectionHandle = $handle;
            return $this;
        }
        
        return $this->collectionHandle;
    }

    public function getItemKey($item): string
    {
        return $item->id();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'md';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relativePath = Str::after($path, $this->directory() . '/');
        $data = $this->extractDataFromFile($contents);
        
        $slug = $this->getSlugFromPath($relativePath);
        $id = $this->getIdFromPath($relativePath);
        
        return Entry::make()
            ->collection($this->collectionHandle)
            ->id($id)
            ->slug($slug)
            ->data($data['data'])
            ->published($data['published'])
            ->date($data['date'])
            ->locale($data['locale'] ?? config('app.locale'));
    }

    protected function extractDataFromFile(string $contents): array
    {
        if (Str::startsWith($contents, '---')) {
            $parts = explode("\n---\n", $contents, 2);
            $frontMatter = trim($parts[0], "- \t\n\r\0\x0B");
            $content = $parts[1] ?? '';
            
            $data = \Symfony\Component\Yaml\Yaml::parse($frontMatter) ?? [];
            
            if (!empty($content)) {
                $data['content'] = $content;
            }
        } else {
            $data = ['content' => $contents];
        }

        $published = $data['published'] ?? true;
        $date = isset($data['date']) ? \Carbon\Carbon::parse($data['date']) : null;
        $locale = $data['locale'] ?? null;

        unset($data['published'], $data['date'], $data['locale']);

        return [
            'data' => $data,
            'published' => $published,
            'date' => $date,
            'locale' => $locale,
        ];
    }

    protected function getFilenameFromKey(string $key): string
    {
        // The key is the entry ID, we need to convert it back to a filename
        // This is a simplified approach - you might need more sophisticated logic
        // depending on your entry naming conventions
        return Str::slug($key) . '.md';
    }

    protected function getItemContents($item): string
    {
        $data = $item->data()->all();
        
        // Add meta fields
        $data['published'] = $item->published();
        if ($item->hasDate()) {
            $data['date'] = $item->date()->toDateTimeString();
        }
        if ($item->locale() !== config('app.locale')) {
            $data['locale'] = $item->locale();
        }

        $content = Arr::pull($data, 'content', '');
        
        $frontMatter = empty($data) ? '' : \Symfony\Component\Yaml\Yaml::dump($data, 2, 2);
        
        if (empty($frontMatter)) {
            return $content;
        }
        
        return "---\n{$frontMatter}---\n{$content}";
    }

    protected function getKeyFromPath(string $path): string
    {
        return $this->getIdFromPath($path);
    }

    protected function getIdFromPath(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        
        // Handle dated entries like "2023-01-01.my-post"
        if (preg_match('/^\d{4}-\d{2}-\d{2}\.(.+)$/', $filename, $matches)) {
            return $matches[1];
        }
        
        // Handle ordered entries like "1.my-post"  
        if (preg_match('/^\d+\.(.+)$/', $filename, $matches)) {
            return $matches[1];
        }
        
        return $filename;
    }

    protected function getSlugFromPath(string $path): string
    {
        $filename = pathinfo($path, PATHINFO_FILENAME);
        
        // Remove date prefix if present
        $slug = preg_replace('/^\d{4}-\d{2}-\d{2}\./', '', $filename);
        
        // Remove order prefix if present
        $slug = preg_replace('/^\d+\./', '', $slug);
        
        return $slug;
    }
}