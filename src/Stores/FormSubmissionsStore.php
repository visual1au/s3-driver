<?php

namespace Statamic\S3Filesystem\Stores;

use Statamic\Forms\Submission;
use Statamic\Support\Str;
use SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Carbon\Carbon;

class FormSubmissionsStore extends BaseS3Store
{
    public function key(): string
    {
        return 'form-submissions';
    }

    protected function getDefaultDirectory(): string
    {
        return 'storage/forms';
    }

    public function getItemKey($item): string
    {
        return $item->form()->handle() . '::' . $item->id();
    }

    public function getItemFilter(SplFileInfo $file): bool
    {
        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relativePath = str_replace($this->directory() . '/', '', $path);
        $pathParts = explode('/', $relativePath);
        
        $formHandle = $pathParts[0];
        $filename = end($pathParts);
        $id = pathinfo($filename, PATHINFO_FILENAME);
        
        $data = Yaml::parse($contents);
        $date = isset($data['date']) ? Carbon::parse($data['date']) : null;
        
        unset($data['date']);

        $form = \Statamic\Facades\Form::find($formHandle);
        
        return Submission::make()
            ->form($form)
            ->id($id)
            ->data($data)
            ->date($date);
    }

    protected function getFilenameFromKey(string $key): string
    {
        [$formHandle, $submissionId] = explode('::', $key, 2);
        return $formHandle . '/' . $submissionId . '.yaml';
    }

    protected function getItemContents($item): string
    {
        $data = $item->data();
        
        if ($item->date()) {
            $data['date'] = $item->date()->toDateTimeString();
        }

        return Yaml::dump($data, 2, 2, Yaml::DUMP_NULL_AS_TILDE);
    }

    protected function getKeyFromPath(string $path): string
    {
        $relativePath = str_replace($this->directory() . '/', '', $path);
        $pathParts = explode('/', $relativePath);
        
        $formHandle = $pathParts[0];
        $filename = end($pathParts);
        $submissionId = pathinfo($filename, PATHINFO_FILENAME);
        
        return $formHandle . '::' . $submissionId;
    }
}