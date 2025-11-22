<?php

namespace App\Models\Chat;

use App\Enums\Chat\ChatMenuActionEnum;
use App\Enums\Chat\ChatMenuActionRedirectEnum;
use App\Filters\Chat\ChatMenuFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Database\Factories\Chat\ChatMenuFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static ChatMenuFactory factory(...$parameters)
 */
class ChatMenu extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use Filterable;
    use SetSortAfterCreate;

    public const ALLOWED_SORTING_FIELDS = [
        'sort',
    ];

    protected $fillable = [
        'action',
        'redirect_to',
        'parent_id',
        'active',
        'sort',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'action' => ChatMenuActionEnum::class,
        'redirect_to' => ChatMenuActionRedirectEnum::class,
        'active' => 'bool'
    ];

    public function modelFilter(): string
    {
        return $this->provideFilter(ChatMenuFilter::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function activeSubMenu(): HasMany
    {
        return $this->subMenu()
            ->where('active', true);
    }

    public function subMenu(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function scopeActive(Builder $builder): void
    {
        $builder->where('active', true);
    }

    public function scopeGeneral(Builder $builder): void
    {
        $builder
            ->whereNull('parent_id')
            ->where(
                fn(Builder $builder) => $builder
                    ->where('action', '<>', ChatMenuActionEnum::SUB_MENU)
                    ->orWhere(
                        fn(Builder $orBuilder) => $orBuilder
                            ->where('action', ChatMenuActionEnum::SUB_MENU)
                            ->whereHas('activeSubMenu')
                    )
            );
    }
}
