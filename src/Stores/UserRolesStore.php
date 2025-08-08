<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\Role;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class UserRolesStore extends BaseS3Store
{
    public function key(): string
    {
        return 'user-roles';
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
        return $file->getBasename() === 'roles.yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = Yaml::parse($contents) ?: [];
        $roles = collect();

        foreach ($data as $handle => $config) {
            $role = Role::make($handle)
                ->title(Arr::get($config, 'title'))
                ->permissions(Arr::get($config, 'permissions', []));
                
            $roles->put($handle, $role);
        }

        return $roles;
    }

    public function paths(): \Illuminate\Support\Collection
    {
        $directory = $this->directory();
        $rolesFile = $directory . '/roles.yaml';
        
        if ($this->s3Filesystem->exists($rolesFile)) {
            return collect(['roles' => $rolesFile]);
        }
        
        return collect();
    }

    public function getItem($key)
    {
        if ($key !== 'roles') {
            return null;
        }
        
        $path = $this->directory() . '/roles.yaml';
        
        if (!$this->s3Filesystem->exists($path)) {
            return collect();
        }
        
        $contents = $this->s3Filesystem->get($path);
        return $this->makeItemFromFile($path, $contents);
    }

    protected function getFilenameFromKey(string $key): string
    {
        return 'roles.yaml';
    }

    protected function getItemContents($item): string
    {
        if ($item instanceof \Illuminate\Support\Collection) {
            $data = [];
            
            foreach ($item as $handle => $role) {
                $data[$handle] = [
                    'title' => $role->title(),
                    'permissions' => $role->permissions(),
                ];
                
                // Remove empty permissions array
                if (empty($data[$handle]['permissions'])) {
                    unset($data[$handle]['permissions']);
                }
            }
            
            return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
        }
        
        return '';
    }

    protected function getKeyFromPath(string $path): string
    {
        return 'roles';
    }
}