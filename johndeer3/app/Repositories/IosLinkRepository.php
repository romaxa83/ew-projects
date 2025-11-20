<?php

namespace App\Repositories;

use App\Abstractions\AbstractRepository;
use App\Models\User\IosLink;
use Illuminate\Database\Eloquent\Builder;

class IosLinkRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return IosLink::query();
    }
}

//class IosLinkRepository extends RepositoryAbstraction
//{
//    public function getModelName(): string
//    {
//        return IosLink::class;
//    }
//}

