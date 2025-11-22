<?php

namespace App\Http\Controllers\Api\BodyShop;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Users\Sync\UserBsResource;
use App\Models\Users\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SyncController extends ApiController
{
    public function users(): AnonymousResourceCollection
    {
        $users = User::query()
            ->withTrashed()
            ->onlyBodyShopUsers()
            ->get();

        return UserBsResource::collection($users);
    }
}
