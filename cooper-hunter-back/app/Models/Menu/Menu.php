<?php

namespace App\Models\Menu;

use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use App\Filters\Menu\MenuFilter;
use App\Models\About\Page;
use App\Models\Admins\Admin;
use App\Models\BaseHasTranslation;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveScopeTrait;
use App\Traits\Model\SetSortAfterCreate;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\Menu\MenuFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int page_id
 * @property string position
 * @property bool active
 * @property int sort
 * @property string block
 *
 * @method static MenuFactory factory(...$parameters)
 */
class Menu extends BaseHasTranslation
{
    use HasFactory;
    use Filterable;
    use SetSortAfterCreate;
    use ActiveScopeTrait;
    use CastsEnums;

    public const TABLE = 'menus';
    public const ALLOWED_SORTING_FIELDS = [
        'sort',
    ];
    public $timestamps = false;
    protected $fillable = [
        'sort',
        'position',
        'block',
        'active',
        'page_id',
    ];
    protected $casts = [
        'position' => MenuPositionEnum::class,
        'block' => MenuBlockEnum::class,
    ];

    public function page(): BelongsTo|Page
    {
        return $this->belongsTo(Page::class);
    }

    public function modelFilter(): string
    {
        return MenuFilter::class;
    }

    public function scopeForGuard(Builder|self $builder, ?Admin $admin): void
    {
        if ($admin) {
            return;
        }

        $builder->where('active', true);
    }
}
