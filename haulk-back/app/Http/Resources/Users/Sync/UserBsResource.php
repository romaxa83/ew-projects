<?php

namespace App\Http\Resources\Users\Sync;

use App\Models\Language;
use App\Models\Users\User;
use App\Services\Permissions\PermissionWorker;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBsResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;

        $worker = new PermissionWorker();
        $permissions = $worker->getPermissionsProfile(
            $worker->getUserPermissions($user)
        );

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'second_name' => $user->second_name,
            'password' => $user->password,
            'status' => $user->status,
            'email' => $user->email,
            'phone' => $user->phone,
            'phone_extension' => $user->phone_extension,
            'phones' => $user->phones,
            'language' => $user->language ?? Language::default()->first()->slug,
            'created_at' => $user->created_at->timestamp,
            'updated_at' => $user->created_at->timestamp,
            'deleted_at' => $user->deleted_at ? $user->deleted_at->timestamp : null,
            'role' => [
                'name' => $user->roles->first()->name
            ],
        ];
    }
}
