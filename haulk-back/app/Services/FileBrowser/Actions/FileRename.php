<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\SuccessActionResource;
use Exception;

class FileRename extends AbstractFileBrowserAction
{
    public const ACTION = 'fileRename';

    private $folder;

    /**
     * @return $this|FileBrowserAction
     * @throws Exception
     */
    public function handle(): FileBrowserAction
    {
        $path = $this->getPath();
        $from = $path . DIRECTORY_SEPARATOR . $this->getName();

        $fileName = $this->getNewName();
        $to = $path . DIRECTORY_SEPARATOR . $fileName;

        $this->checkPathExists($to);

        if ($this->hasErrors()) {
            return $this;
        }

        $this->fileBrowser->moveFile($from, $to);

        return $this;
    }

    public function response()
    {
        if ($this->hasErrors()) {
            return $this->getErrorResponse();
        }

        return SuccessActionResource::make($this->folder);
    }
}
