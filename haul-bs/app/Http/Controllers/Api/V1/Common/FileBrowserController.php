<?php

namespace App\Http\Controllers\Api\V1\Common;

use App\Foundations\Modules\Permission\Models\Role;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\FileManager\FileBrowserRequest;
use App\Http\Requests\FileManager\FileUploadRequest;
use App\Services\FileBrowser\ActionFactory;
use App\Services\FileBrowser\FileUploadFactory;
use App\Services\FileBrowser\NotFoundActionException;
use Illuminate\Http\Resources\Json\JsonResource;

class FileBrowserController extends ApiController
{
    public function __construct()
    {
        if (config('filebrowser.need_auth')) {
            $this->middleware(['filebrowser_auth', 'auth:'.Role::GUARD_USER]);
        }
    }

    public function upload(FileUploadRequest $request, FileUploadFactory $factory)
    {
        return $factory
            ->create($request->getDto())
            ->handle()
            ->response();
    }

    /**
     * @param FileBrowserRequest $request
     * @param ActionFactory $factory
     * @return JsonResource
     * @throws NotFoundActionException
     */
    public function browse(FileBrowserRequest $request, ActionFactory $factory)
    {
        return $factory
            ->create($request->getDto())
            ->handle()
            ->response();
    }
}

