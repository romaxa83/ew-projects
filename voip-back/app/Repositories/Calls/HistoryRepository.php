<?php

namespace App\Repositories\Calls;

use App\Models\Calls\History;
use App\Repositories\AbstractRepository;

final class HistoryRepository extends AbstractRepository
{
    public function modelClass(): string
    {
        return History::class;
    }
}

