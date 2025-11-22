<?php

namespace App\Models\Orders\Categories;

use App\Contracts\Roles\HasGuardUser;
use App\Filters\Orders\Categories\OrderCategoryFilter;
use App\Models\Admins\Admin;
use App\Models\BaseHasTranslation;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPart;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Orders\Categories\OrderCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string|null guid
 * @property int sort
 * @property bool active
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 * @property bool is_default
 * @property bool need_description
 *
 * @method static OrderCategoryFactory factory(...$parameters)
 */
class OrderCategory extends BaseHasTranslation
{
    use HasFactory;
    use SetSortAfterCreate;
    use ActiveScopeTrait;
    use Filterable;

    public const TABLE = 'order_categories';

    protected $fillable = [
        'id',
        'guid',
        'sort',
        'active',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function modelFilter(): string
    {
        return OrderCategoryFilter::class;
    }

    public function scopeForGuard(Builder|self $build, ?HasGuardUser $auth): void
    {
        if ($auth instanceof Admin) {
            $build->where('is_default', false);
        } else {
            $build->where('active', true);
        }
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(
            Order::class,
            OrderPart::TABLE,
            'order_category_id',
            'order_id',
            'id',
            'id'
        );
    }
}
