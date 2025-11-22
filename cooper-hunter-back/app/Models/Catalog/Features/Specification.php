<?php

namespace App\Models\Catalog\Features;

use App\Filters\Catalog\Features\SpecificationFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Features\SpecificationFactory;

/**
 * @property int id
 * @property bool active
 * @property int sort
 * @property string icon
 *
 * @method static SpecificationFactory factory(...$parameters)
 */
class Specification extends BaseModel
{
    use HasTranslations;
    use HasFactory;
    use Filterable;
    use SetSortAfterCreate;

    public const TABLE = 'specifications';
    public const MORPH_NAME = 'specification';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'sort'
    ];

    public function modelFilter(): string
    {
        return SpecificationFilter::class;
    }
}
