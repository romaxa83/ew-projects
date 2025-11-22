<?php

namespace App\Repositories\Page;

use App\Models\Page\Page;
use App\Repositories\AbstractRepository;

class PageRepository extends AbstractRepository
{
    public function query()
    {
        return Page::query();
    }
}

