<?php

namespace App\Models\Faq;

use App\Filters\Faq\FaqFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Faq\FaqFactory;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property bool active
 * @property int sort
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @method static FaqFactory factory(...$parameters)
 */
class Faq extends BaseModel
{
    use Filterable;
    use HasFactory;
    use HasTranslations;
    use SetSortAfterCreate;

    public const TABLE = 'faqs';

    protected $table = self::TABLE;

    protected $fillable = [
        'sort'
    ];

    public function modelFilter(): string
    {
        return FaqFilter::class;
    }
}
