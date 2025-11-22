<?php

namespace App\Services\FileBrowser\Actions;

use App\Dto\FileBrowser\FolderDto;
use App\Http\Resources\FileBrowser\DirectoryResource;

class Folders extends AbstractFileBrowserAction
{

    public const ACTION = 'folders';

    /**
     * @var FolderDto
     */
    private $folder;

    public function handle(): FileBrowserAction
    {
        $path = $this->getPath();

        $this->mapFolders($path);

        return $this;
    }

    private function mapFolders(string $path)
    {
        $folders = empty($path) ? ['.'] : ['..'];

        foreach ($this->fileBrowser->directories($path) as $directory) {
            $folders[] = $this->fileBrowser->getNameByPath($directory);
        }

        $name = $this->fileBrowser->getNameByPath($path);
        $baseUrl = $this->fileBrowser->getUrl($path);

        $this->folder = FolderDto::byParams($name, $baseUrl, $folders, [], $path);
    }

    public function response()
    {
        return DirectoryResource::make($this->folder);
    }

}
