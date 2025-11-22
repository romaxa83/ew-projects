<?php

namespace App\Entities\Users;

use App\Models\Companies\Company;
use App\Models\Users\User;

class UserStateEntity
{
    public function __construct(private User $user)
    {
    }

    public function getCompany(): Company
    {
        return $this->getUser()->company;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
