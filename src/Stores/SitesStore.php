<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Sites\Site;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class SitesStore extends BaseS3Store
{
    public function key(): string
    {
        return 'sites';
    }

    protected function getDefaultDirectory(): string
    {
        return 'resources';
    }

    public function getItemKey($item): string
    {
        return $item->handle();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getBasename() === 'sites.yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = Yaml::parse($contents) ?: [];
        $sites = collect();

        foreach ($data as $handle => $config) {
            $site = new Site($handle, $config);
            $sites->put($handle, $site);
        }

        return $sites;
    }

    public function paths(): \Illuminate\Support\Collection
    {
        $directory = $this->directory();
        $sitesFile = $directory . '/sites.yaml';
        
        if ($this->s3Filesystem->exists($sitesFile)) {
            return collect(['sites' => $sitesFile]);
        }
        
        return collect();
    }

    public function getItem($key)
    {
        if ($key !== 'sites') {
            return null;
        }
        
        $path = $this->directory() . '/sites.yaml';
        
        if (!$this->s3Filesystem->exists($path)) {
            return collect();
        }
        
        $contents = $this->s3Filesystem->get($path);
        return $this->makeItemFromFile($path, $contents);
    }

    protected function getFilenameFromKey(string $key): string
    {
        return 'sites.yaml';
    }

    protected function getItemContents($item): string
    {
        if ($item instanceof \Illuminate\Support\Collection) {
            $data = [];
            
            foreach ($item as $handle => $site) {
                $data[$handle] = [
                    'name' => $site->name(),
                    'url' => $site->url(),
                    'locale' => $site->locale(),
                ];
                
                // Add additional site attributes
                if ($site->attributes()) {
                    $data[$handle] = array_merge($data[$handle], $site->attributes());
                }
            }
            
            return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
        }
        
        return '';
    }

    protected function getKeyFromPath(string $path): string
    {
        return 'sites';
    }
}