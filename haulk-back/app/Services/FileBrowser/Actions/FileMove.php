<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\SuccessActionResource;
use Exception;

class FileMove extends AbstractFileBrowserAction
{
    public const ACTION = 'fileMove';

    private $folder;

    /**
     * @return $this|FileBrowserAction
     * @throws Exception
     */
    public function handle(): FileBrowserAction
    {
        $from = $this->getFrom();
        $fileName = $this->fileBrowser->getNameByPath($this->getFrom());
        $to = $this->getPath() . DIRECTORY_SEPARATOR . $fileName;

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
