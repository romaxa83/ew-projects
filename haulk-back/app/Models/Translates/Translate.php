<?php


namespace App\Models\Translates;


use App\ModelFilters\Translates\TranslateFilter;
use App\Traits\ModelMain;
use Illuminate\Support\Carbon;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Translates\Translate
 *
 * @property int $id
 * @property string $key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TranslateTranslates $current
 * @property-read Collection|TranslateTranslates[] $data
 * @property-read int|null $data_count
 * @method static Builder|Translate newModelQuery()
 * @method static Builder|Translate newQuery()
 * @method static Builder|Translate query()
 * @method static Builder|Translate whereCreatedAt($value)
 * @method static Builder|Translate whereId($value)
 * @method static Builder|Translate whereKey($value)
 * @method static Builder|Translate whereUpdatedAt($value)
 * @mixin Eloquent
 * @method static Builder|Translate filter($input = [], $filter = null)
 * @method static Builder|Translate paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Translate simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Translate whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Translate whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Translate whereLike($column, $value, $boolean = 'and')
 */
class Translate extends Model
{
    use ModelMain;
    use Filterable;

    CONST TABLE_NAME = 'translates';

    public $fillable = ['key'];

    protected $table = self::TABLE_NAME;

    public function modelFilter()
    {
        return $this->provideFilter(TranslateFilter::class);
    }
}
