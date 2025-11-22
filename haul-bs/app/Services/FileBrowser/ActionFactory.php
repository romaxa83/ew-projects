<?php

namespace App\Services\FileBrowser;

use App\Dto\FileBrowser\FileBrowserDto;
use App\Services\FileBrowser\Actions\FileMove;
use App\Services\FileBrowser\Actions\FileRemove;
use App\Services\FileBrowser\Actions\FileRename;
use App\Services\FileBrowser\Actions\FolderCreate;
use App\Services\FileBrowser\Actions\FileBrowserAction;
use App\Services\FileBrowser\Actions\Files;
use App\Services\FileBrowser\Actions\FolderMove;
use App\Services\FileBrowser\Actions\FolderRemove;
use App\Services\FileBrowser\Actions\FolderRename;
use App\Services\FileBrowser\Actions\Folders;
use App\Services\FileBrowser\Actions\Permissions;

class ActionFactory
{
    /**
     * @param FileBrowserDto $dto
     * @return FileBrowserAction
     * @throws NotFoundActionException
     */
    public function create(FileBrowserDto $dto): FileBrowserAction
    {
        switch ($dto->getAction()) {
            case Files::ACTION:
                return new Files($dto);
            case FileRename::ACTION:
                return new FileRename($dto);
            case FileMove::ACTION:
                return new FileMove($dto);
            case FileRemove::ACTION:
                return new FileRemove($dto);
            case Folders::ACTION:
                return new Folders($dto);
            case FolderCreate::ACTION:
                return new FolderCreate($dto);
            case FolderMove::ACTION:
                return new FolderMove($dto);
            case FolderRemove::ACTION:
                return new FolderRemove($dto);
            case FolderRename::ACTION:
                return new FolderRename($dto);
            case Permissions::ACTION:
                return new Permissions($dto);
        }

        throw new NotFoundActionException('Not found handler for with file browser action!');
    }
}
