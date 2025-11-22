<?php

namespace App\Repositories\Support;

use App\Models\Support\Category;
use App\Repositories\AbstractRepository;

class CategoryRepository extends AbstractRepository
{
    public function query()
    {
        return Category::query();
    }
}
