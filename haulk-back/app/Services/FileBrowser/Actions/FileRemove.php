<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\SuccessActionResource;

class FileRemove extends AbstractFileBrowserAction
{
    public const ACTION = 'fileRemove';

    private $folder;

    public function handle(): FileBrowserAction
    {
        $path = $this->getPath();
        $removingFilePath = $path . DIRECTORY_SEPARATOR . $this->getName();
        $this->fileBrowser->removeFile($removingFilePath);

        return $this;
    }

    public function response()
    {
        return SuccessActionResource::make($this->folder);
    }
}
