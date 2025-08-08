<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\UserGroup;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class UserGroupsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'user-groups';
    }

    protected function getDefaultDirectory(): string
    {
        return 'resources/users';
    }

    public function getItemKey($item): string
    {
        return $item->handle();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getBasename() === 'groups.yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = Yaml::parse($contents) ?: [];
        $groups = collect();

        foreach ($data as $handle => $config) {
            $group = UserGroup::make($handle)
                ->title(Arr::get($config, 'title'))
                ->roles(Arr::get($config, 'roles', []));
                
            $groups->put($handle, $group);
        }

        return $groups;
    }

    public function paths(): \Illuminate\Support\Collection
    {
        $directory = $this->directory();
        $groupsFile = $directory . '/groups.yaml';
        
        if ($this->s3Filesystem->exists($groupsFile)) {
            return collect(['groups' => $groupsFile]);
        }
        
        return collect();
    }

    public function getItem($key)
    {
        if ($key !== 'groups') {
            return null;
        }
        
        $path = $this->directory() . '/groups.yaml';
        
        if (!$this->s3Filesystem->exists($path)) {
            return collect();
        }
        
        $contents = $this->s3Filesystem->get($path);
        return $this->makeItemFromFile($path, $contents);
    }

    protected function getFilenameFromKey(string $key): string
    {
        return 'groups.yaml';
    }

    protected function getItemContents($item): string
    {
        if ($item instanceof \Illuminate\Support\Collection) {
            $data = [];
            
            foreach ($item as $handle => $group) {
                $data[$handle] = [
                    'title' => $group->title(),
                    'roles' => $group->roles()->map->handle()->all(),
                ];
                
                // Remove empty roles array
                if (empty($data[$handle]['roles'])) {
                    unset($data[$handle]['roles']);
                }
            }
            
            return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
        }
        
        return '';
    }

    protected function getKeyFromPath(string $path): string
    {
        return 'groups';
    }
}