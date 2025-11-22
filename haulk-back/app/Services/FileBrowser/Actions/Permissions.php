<?php

namespace App\Services\FileBrowser\Actions;

use App\Http\Resources\FileBrowser\PermissionsResource;

class Permissions extends AbstractFileBrowserAction
{
    public const ACTION = 'permissions';

    public function handle(): FileBrowserAction
    {
        return $this;
    }

    public function response(): PermissionsResource
    {
        return PermissionsResource::make([]);
    }
}
