<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\FileActionErrorResource;

class FolderMove extends AbstractFileBrowserAction
{
    public const ACTION = 'folderMove';

    public function handle(): FileBrowserAction
    {
        return $this->addError(__('Moving directories is not possible!'));
    }

    public function response()
    {
        return FileActionErrorResource::make($this->getErrors());
    }
}
