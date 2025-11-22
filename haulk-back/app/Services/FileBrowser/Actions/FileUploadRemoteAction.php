<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\FileUploadResource;

class FileUploadRemoteAction extends AbstractFileUploadAction
{
    public const ACTION = 'fileUploadRemote';

    private $remoteFiles;

    public function handle(): FileBrowserAction
    {
        return $this;
    }

    public function response()
    {
        return FileUploadResource::make($this->remoteFiles);
    }
}
