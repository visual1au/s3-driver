<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Term;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use SplFileInfo;

class TaxonomyTermsStore extends BaseS3Store
{
    protected string $taxonomyHandle;

    public function key(): string
    {
        return "taxonomy-terms-{$this->taxonomyHandle}";
    }

    protected function getDefaultDirectory(): string
    {
        return "taxonomies/{$this->taxonomyHandle}";
    }

    public function taxonomyHandle(string $handle = null): string
    {
        if ($handle !== null) {
            $this->taxonomyHandle = $handle;
            return $this;
        }
        
        return $this->taxonomyHandle;
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
        
        return Term::make()
            ->taxonomy($this->taxonomyHandle)
            ->slug($slug)
            ->data($data['data'])
            ->published($data['published'])
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
        $locale = $data['locale'] ?? null;

        unset($data['published'], $data['locale']);

        return [
            'data' => $data,
            'published' => $published,
            'locale' => $locale,
        ];
    }

    protected function getFilenameFromKey(string $key): string
    {
        return Str::slug($key) . '.md';
    }

    protected function getItemContents($item): string
    {
        $data = $item->data()->all();
        
        // Add meta fields
        $data['published'] = $item->published();
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
        return $this->getSlugFromPath($path);
    }

    protected function getSlugFromPath(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}