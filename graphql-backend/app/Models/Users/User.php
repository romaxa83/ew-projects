<?php

namespace App\Models\Users;

use App\Casts\EmailCast;
use App\Entities\Messages\AlertMessageEntity;
use App\Entities\Users\UserStateEntity;
use App\Filters\Companies\UserFilter;
use App\Models\BaseAuthenticatable;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Languageable;
use App\Models\ListPermission;
use App\Permissions\Employees\EmployeeDeletePermission;
use App\Permissions\Employees\EmployeeUpdatePermission;
use App\Traits\AddSelectTrait;
use App\Traits\Eloquent\WhereCompanyTrait;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Localization\LanguageRelation;
use App\Traits\Localization\SetLanguageTrait;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Carbon\Carbon;
use Core\Services\AlertMessages\CustomAlertMessageService;
use Core\WebSocket\Contracts\Subscribable;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection as BaseCollection;

/**
 * @property int id
 * @property string first_name
 * @property string last_name
 * @property string middle_name
 * @property string password
 *
 * @property null|string email_verification_code
 *
 * @property Email email
 * @property Carbon email_verified_at
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see User::company()
 * @property-read Company company
 *
 * @see User::companyUser()
 * @property-read CompanyUser companyUser
 *
 * @method static static|Builder query()
 *
 * @method Collection|static[] get()
 *
 * @method static UserFactory factory(...$options)
 *
 * @mixin Model
 */
class User extends BaseAuthenticatable implements Languageable, ListPermission, Subscribable
{
    use HasFactory;
    use HasRoles;
    use Filterable;
    use Notifiable;
    use SetPasswordTrait;
    use SetLanguageTrait;
    use LanguageRelation;
    use AddSelectTrait;
    use WhereCompanyTrait;
    use DefaultListPermissionTrait;

    public const GUARD = 'graph_user';

    public const MIN_LENGTH_PASSWORD = 8;

    public const MORPH_NAME = 'user';

    public const TABLE = 'users';

    public const ALLOWED_SORTING_FIELDS = [
        'name',
        'email',
        'roles',
        'company',
        'created_at',
    ];

    public const ALLOWED_SORTING_FIELDS_RELATIONS = [
        'roles' => 'roles.translate.title',
    ];

    protected static array $eagerLoadingFields = [
        'language' => 'lang',
    ];

    protected $table = self::TABLE;

    protected $fillable = [
        'name',
        'email',
        'password',
        'lang',
        'first_name',
        'last_name',
        'middle_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'email' => EmailCast::class,
    ];

    public function getUniqId(): string
    {
        return $this->getMorphClass() . '.' . $this->getKey();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function company(): HasOneThrough|Company
    {
        return $this->hasOneThrough(
            Company::class,
            CompanyUser::class,
            'user_id',
            'id',
            'id',
            'company_id'
        );
    }

    public function companyUser(): HasOne|CompanyUser
    {
        return $this->hasOne(CompanyUser::class, 'user_id', 'id');
    }

    public function hasCompany(): bool
    {
        return (bool)$this->company;
    }

    public function isSameCompany(Company|int $company): bool
    {
        return $this->company->id === to_model_key($company);
    }

    public function modelFilter(): string
    {
        return UserFilter::class;
    }

    public function getEmailString(): string
    {
        return (string)$this->getEmail();
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getName(): string
    {
        $fullName = sprintf('%s %s %s', $this->first_name, $this->last_name, $this->middle_name);

        return str_replace(' ', ' ', $fullName);
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function toArray(): array
    {
        $result = parent::toArray();

        $result['active'] = $this->isActive();

        return $result;
    }

    public function isActive(): bool
    {
        return $this->isEmailVerified();
    }

    public function isEmailVerified(): bool
    {
        return (bool)$this->email_verified_at;
    }

    public function getLangSlug(): ?string
    {
        return $this->lang;
    }

    public function canBeDeleted(): bool
    {
        if (!is_prod() && config('grants.permissions_disable')) {
            return true;
        }

        return !$this->isOwner();
    }

    public function isOwner(): bool
    {
        return $this->companyUser->state === Company::STATE_OWNER;
    }

    public function getDeletePermissionKey(): string
    {
        return EmployeeDeletePermission::KEY;
    }

    public function getUpdatePermissionKey(): string
    {
        return EmployeeUpdatePermission::KEY;
    }

    public function getState(): UserStateEntity
    {
        return new UserStateEntity($this);
    }

    /**
     * @return BaseCollection<AlertMessageEntity>
     */
    public function getAlerts(): BaseCollection
    {
        return app(CustomAlertMessageService::class)->getForUser($this);
    }
}
