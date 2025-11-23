<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use WezomCms\Core\Traits\Model\Filterable;
use WezomCms\Users\Models\User;

/**
 * WezomCms\Users\Orders\UserAddress
 *
 * @property int $id
 * @property int $user_id
 * @property bool $primary
 * @property string $region_code
 * @property string $region
 * @property string $city_code
 * @property string $city
 * @property string|null $postal_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $address
 * @property string $name
 * @property-read User $user
 * @method static Builder|UserAddress filter(array $input = [], $filter = null)
 * @method static Builder|UserAddress newModelQuery()
 * @method static Builder|UserAddress newQuery()
 * @method static Builder|UserAddress paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|UserAddress query()
 * @method static Builder|UserAddress simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|UserAddress whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|UserAddress whereCity($value)
 * @method static Builder|UserAddress whereCreatedAt($value)
 * @method static Builder|UserAddress whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|UserAddress whereHouse($value)
 * @method static Builder|UserAddress whereId($value)
 * @method static Builder|UserAddress whereLike(string $column, string $value, string $boolean = 'and')
 * @method static Builder|UserAddress wherePrimary($value)
 * @method static Builder|UserAddress whereRoom($value)
 * @method static Builder|UserAddress whereStreet($value)
 * @method static Builder|UserAddress whereUpdatedAt($value)
 * @method static Builder|UserAddress whereUserId($value)
 * @mixin Eloquent
 */
class UserAddress extends Model
{
    use Filterable;
    use HasFactory;

    public const TABLE = 'user_addresses';

    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'primary',
        'region_code',
        'region',
        'city_code',
        'city',
        'postal_code',
        'address',
        'name',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['primary' => 'bool'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
