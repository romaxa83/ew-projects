<?php

namespace App\Services\FileBrowser\Actions;

use App\Dto\FileBrowser\FileDto;
use App\Dto\FileBrowser\FolderDto;
use App\Http\Resources\FileBrowser\DirectoryResource;
use Cache;
use Illuminate\Support\Carbon;

class Files extends AbstractFileBrowserAction
{
    public const ACTION = 'files';

    protected FolderDto $folder;

    public function handle(): FileBrowserAction
    {
        $path = $this->getPath();

        $this->mapFiles($path);

        return $this;
    }

    protected function mapFiles(string $path)
    {
        $files = [];

        foreach ($this->fileBrowser->files($path) as $filePath) {
            $files[] = FileDto::byAttributes(
                $this->getAttributesByPath($filePath)
            );
        }

        $this->folder = FolderDto::byParams(
            $this->fileBrowser->getNameByPath($path),
            $this->fileBrowser->getUrl('/'),
            [],
            $files,
            $path
        );
    }

    protected function getAttributesByPath(string $filePath): array
    {
        return Cache::remember(
            config('filebrowser.cache.key') . $filePath,
            config('filebrowser.cache.duration'),
            function () use ($filePath): array {
                return [
                    'fileName' => $this->getNameByFilePath($filePath),
                    'thumb' => !$this->isImage($filePath)
                        ? $this->getThumbByFilePath($filePath)
                        : null,
                    'changed' => $this->getChangedTimeByFilePath($filePath),
                    'size' => $this->getSizeByFilePath($filePath),
                ];
            }
        );
    }

    protected function getNameByFilePath(string $filePath): string
    {
        return $this->fileBrowser->getNameByPath($filePath);
    }

    private function isImage($filePath): bool
    {
        return isImage(
            $this->fileBrowser->getExtension($filePath)
        );
    }

    protected function getThumbByFilePath(string $path): string
    {
        $extension = $this->fileBrowser->getExtension($path);

        $url = config('filebrowser.thumb.dir_url');

        if (in_array($extension, config('filebrowser.thumb.exists'))) {
            return $url . sprintf(config('filebrowser.thumb.mask'), $extension);
        }

        return $url . config('filebrowser.thumb.unknown_extension');
    }

    protected function getChangedTimeByFilePath(string $filePath): Carbon
    {
        return Carbon::createFromTimestamp(
            $this->fileBrowser->lastModified($filePath)
        );
    }

    protected function getSizeByFilePath(string $filePath): int
    {
        return $this->fileBrowser->size($filePath);
    }

    public function response(): DirectoryResource
    {
        return DirectoryResource::make($this->folder);
    }
}
