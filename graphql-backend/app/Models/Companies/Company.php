<?php

namespace App\Models\Companies;

use App\Filters\Companies\CompanyFilter;
use App\Models\BaseModel;
use App\Models\ListPermission;
use App\Models\Users\User;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Localization\SetLanguageTrait;
use App\Traits\Permissions\DefaultListPermissionTrait;
use Carbon\Carbon;
use Database\Factories\Companies\CompanyFactory;
use Illuminate\Cache\TaggedCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Cache;

/**
 * @property int id
 * @property string name
 * @property string lang
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int currency_id
 *
 * @see Company::users()
 * @property-read Collection|User[] users
 *
 * @see Company::owner()
 * @property-read User owner
 *
 * @see Company::scopeWhereSameCompany()
 * @method Builder|static whereSameCompany($user)
 *
 * @see Company::scopeUnderConsideration()
 * @method Builder|static underConsideration()
 *
 * @see Company::scopeActive()
 * @method Builder|static active()
 *
 * @see Company::scopeInactive()
 * @method Builder|static inactive()
 *
 * @method static CompanyFactory factory(int $number = null)
 */
class Company extends BaseModel implements ListPermission
{
    use Filterable;
    use HasFactory;
    use SetLanguageTrait;
    use LanguageRelation;
    use DefaultListPermissionTrait;

    public const TABLE = 'companies';
    public const STATE_OWNER = 'owner';

    public const ALLOWED_SORTING_FIELDS = [
        'name',
        'email',
        'users',
        'created_at',
        'status',
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'lang',
    ];

    public function modelFilter(): string
    {
        return CompanyFilter::class;
    }

    public function scopeWhereSameCompany(Builder $q, User|int $user): void
    {
        if (is_int($user)) {
            $q->whereHas('users', fn(Builder $q) => $q->whereKey($user));

            return;
        }

        $q->whereKey($user->company->getId());
    }

    public function scopeActive(Builder|self $b): void
    {
        $b->where('is_active', true);
    }

    public function scopeInactive(Builder|self $b): void
    {
        $b->where('is_active', false);
    }

    public function canBeDeleted(): bool
    {
        return false;
    }

    public function owner(): HasOneThrough|User
    {
        return $this->hasOneThrough(
            User::class,
            CompanyUser::class,
            'company_id',
            'id',
            'id',
            'user_id'
        )
            ->where(CompanyUser::TABLE . '.state', self::STATE_OWNER);
    }

    public function users(): BelongsToMany|User
    {
        return $this->belongsToMany(User::class);
    }

    public function clearCache(): void
    {
        $this->cache()->flush();
    }

    public function cache(): TaggedCache
    {
        return Cache::tags($this->getCacheTags());
    }

    public function getCacheTags(): array
    {
        return [
            'company_id_' . $this->getKey(),
        ];
    }

    public function getName(): string
    {
        return $this->name
            ?? $this->owner?->getName()
            ?? $this->getId();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
