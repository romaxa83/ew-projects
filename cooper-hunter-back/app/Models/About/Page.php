<?php

namespace App\Models\About;

use App\Contracts\Media\HasMedia;
use App\Enums\Orders\Dealer\PaymentType;
use App\Filters\About\PageFilter;
use App\Models\Admins\Admin;
use App\Models\BaseModel;
use App\Models\Menu\Menu;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\Media\InteractsWithMedia;
use Database\Factories\About\PageFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string slug
 * @property bool active
 * @property bool is_page
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static PageFactory factory(...$parameters)
 */
class Page extends BaseModel implements HasMedia
{
    use HasFactory;
    use HasTranslations;
    use InteractsWithMedia;
    use Filterable;

    public const TABLE = 'pages';
    protected $table = self::TABLE;

    protected $fillable = [
        'active',
        'created_at',
        'updated_at',
        'slug',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_page' => 'boolean'
    ];

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function modelFilter(): string
    {
        return PageFilter::class;
    }

    public function scopeForGuard(Builder|self $builder, ?Admin $admin): void
    {
        if ($admin) {
            return;
        }

        $builder->where('active', true);
    }

    public function scopePage($builder): void
    {
        $builder->where('is_page', true);
    }

    public function scopePaymentDesc($builder): void
    {
        $builder->whereIn('slug', PaymentType::asArray());
    }
}
