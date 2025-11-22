<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\FileActionErrorResource;
use App\Http\Resources\FileBrowser\FolderCreatedResource;
use App\Validators\FileBrowser\DirectoryNestingValidator;

class FolderCreate extends AbstractFileBrowserAction
{
    public const ACTION = 'folderCreate';

    private $folder;

    public function handle(): FileBrowserAction
    {
        $path = $this->getPath();

        $directoryNestingValidator = new DirectoryNestingValidator(config('filebrowser.nesting_limit'));
        if (!$directoryNestingValidator->passes('path', $path)) {
            $this->addError($directoryNestingValidator->message());

            return $this;
        }

        $newFolderPath = $path . DIRECTORY_SEPARATOR . $this->getName();
        $this->fileBrowser->makeDirectory($newFolderPath);

        return $this;
    }

    public function response()
    {
        if ($this->hasErrors()) {
            return FileActionErrorResource::make($this->getErrors());
        }

        return FolderCreatedResource::make($this->folder);
    }

}
