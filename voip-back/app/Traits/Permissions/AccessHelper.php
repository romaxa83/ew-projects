<?php

namespace App\Traits\Permissions;

use Core\Exceptions\TranslatedException;
use Illuminate\Support\Facades\Auth;

trait AccessHelper
{
    public function checkSuperAdmin(): void
    {
        if(!$this->user()->isSuperAdmin()){
            throw new TranslatedException(__('exceptions.admin.not_access'));
        }
    }

    public function checkOwnerAccount($id): void
    {
        if(!$this->user()->isSuperAdmin() && ((int)$id !== $this->user()->id)){
            throw new TranslatedException(__('exceptions.admin.not_access'));
        }
    }

    public function assetSuperAdmin(): bool
    {
        $user = Auth::user() ?? $this->user();
        if($user == null){
            return false;
        }

        return $user->isSuperAdmin();
    }
}
