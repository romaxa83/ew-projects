<?php

namespace App\Models\Catalog\Labels;

use App\Enums\Catalog\Labels\ColorType;
use App\Filters\Catalog\Labels\LabelFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Catalog\Labels\LabelFactory;

/**
 * @property int id
 * @property int sort
 * @property bool active
 * @property ColorType color_type
 *
 * @method static LabelFactory factory(...$parameters)
 */
class Label extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use SetSortAfterCreate;
    use Filterable;

    public $timestamps = false;

    protected $table = self::TABLE;
    public const TABLE = 'catalog_labels';

    protected $fillable = [
        'sort',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'color_type' => ColorType::class,
    ];

    public function modelFilter(): string
    {
        return LabelFilter::class;
    }
}
