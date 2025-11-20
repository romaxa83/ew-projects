<?php

namespace App\Models\User;

use App\ModelFilters\User\NationalityFilter;
use App\Models\BaseModel;
use App\Traits\ActiveTrait;
use EloquentFilter\Filterable;

/**
 * @property int $id
 * @property string $alias
 * @property string $name
 * @property boolean $active
 */

class Nationality extends BaseModel
{
    use ActiveTrait;
    use Filterable;

    public $timestamps = false;

    const TABLE = 'nationalities';
    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(NationalityFilter::class);
    }
}
