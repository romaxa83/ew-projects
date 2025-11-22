<?php

namespace App\Services\FileBrowser;

use App\Dto\FileBrowser\FileUploadDto;
use App\Services\FileBrowser\Actions\FileBrowserAction;
use App\Services\FileBrowser\Actions\FileUploadAction;
//use App\Services\FileBrowser\Actions\FileUploadRemoteAction;

class FileUploadFactory
{
    /**
     * @param FileUploadDto $dto
     * @return FileBrowserAction
//     * @throws NotFoundActionException
     */
    public function create(FileUploadDto $dto): FileBrowserAction
    {
        return new FileUploadAction($dto);

//        switch ($dto->getAction()) {
//            case FileUploadAction::ACTION:
//                return new FileUploadAction($dto);
//            case FileUploadRemoteAction::ACTION:
//                return new FileUploadRemoteAction($dto);
//        }
//
//        throw new NotFoundActionException('Not found handler for this file upload action!');
    }
}
