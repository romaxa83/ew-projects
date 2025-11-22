<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\SuccessActionResource;

class FolderRemove extends AbstractFileBrowserAction
{
    public const ACTION = 'folderRemove';

    private $folder;

    public function handle(): FileBrowserAction
    {
        $path = $this->getPath();
        $removedFolderPath = $path . DIRECTORY_SEPARATOR . $this->getName();
        $this->fileBrowser->removeDirectory($removedFolderPath);

        return $this;
    }

    public function response()
    {
        return SuccessActionResource::make($this->folder);
    }
}
