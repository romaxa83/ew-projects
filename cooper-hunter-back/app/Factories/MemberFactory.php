<?php

namespace App\Factories;

use App\Enums\Member\MemberEnum;
use App\Repositories\Dealers\DealerRepository;
use App\Repositories\Technician\TechnicianRepository;
use App\Repositories\Users\UserRepository;

class MemberFactory
{
    public function __construct(protected MemberEnum $member)
    {}

    public function getRepo()
    {
        if($this->member->isUser()){
            return app(UserRepository::class);
        }
        if($this->member->isDealer()){
            return app(DealerRepository::class);
        }
        if($this->member->isTechnician()){
            return app(TechnicianRepository::class);
        }
    }
}
