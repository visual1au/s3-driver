<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Facades\User;
use Statamic\Support\Arr;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class UsersStore extends BaseS3Store
{
    public function key(): string
    {
        return 'users';
    }

    protected function getDefaultDirectory(): string
    {
        return 'users';
    }

    public function getItemKey($item): string
    {
        return $item->email();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        $relative = $file->getRelativePathname();
        
        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $email = pathinfo($path, PATHINFO_FILENAME);
        $data = Yaml::parse($contents);

        return User::make()
            ->email($email)
            ->id(Arr::get($data, 'id'))
            ->data(Arr::except($data, ['id', 'password_hash']))
            ->passwordHash(Arr::get($data, 'password_hash'))
            ->preferences(Arr::get($data, 'preferences', []))
            ->roles(Arr::get($data, 'roles', []))
            ->groups(Arr::get($data, 'groups', []))
            ->super(Arr::get($data, 'super', false));
    }

    protected function getFilenameFromKey(string $key): string
    {
        return $key . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = $item->data()->all();
        
        // Add user-specific fields
        $data['id'] = $item->id();
        $data['super'] = $item->isSuper();
        
        if ($item->passwordHash()) {
            $data['password_hash'] = $item->passwordHash();
        }
        
        if ($item->roles()->isNotEmpty()) {
            $data['roles'] = $item->roles()->map->handle()->all();
        }
        
        if ($item->groups()->isNotEmpty()) {
            $data['groups'] = $item->groups()->map->handle()->all();
        }
        
        if ($item->preferences()->isNotEmpty()) {
            $data['preferences'] = $item->preferences()->all();
        }

        // Remove null values
        $data = array_filter($data, function ($value) {
            return $value !== null && $value !== [] && $value !== '';
        });

        return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
}