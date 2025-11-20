<?php

namespace App\Filters\Musics;

use App\Models\Departments\Department;
use App\Traits\Filter\ActiveFilter;
use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;

/**
 * @mixin Department
 */
class MusicFilter extends ModelFilter
{
    use IdFilterTrait;
    use ActiveFilter;
}

