<?php

namespace App\Models\Contacts;

use App\ModelFilters\Contacts\ContactFilter;
use App\Models\Locations\State;
use App\Models\Users\User;
use App\Repositories\Locations\StateRepository;
use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Database\Factories\Contacts\ContactFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Models\Contacts\Contact
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $user_id
 * @property int $state_id
 * @property string $full_name
 * @property string $phones
 * @property string|null $address
 * @property string|null $city
 * @property string|null $zip
 * @property string|null $contact
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $fax
 * @property string|null $deleted_at
 * @property string comment_date
 * @property string comment
 *
 * @see Contact::state()
 * @property State $state
 *
 * @see self::scopeFindByPhone()
 * @method static Builder|self findByPhone(string $phone)
 *
 * @method static Builder|Contact filter($input = [], $filter = null)
 * @method static Builder|Contact newModelQuery()
 * @method static Builder|Contact newQuery()
 * @method static Builder|Contact paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Contact query()
 * @method static Builder|Contact simplePaginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|Contact whereAddress($value)
 * @method static Builder|Contact whereBeginsWith($column, $value, $boolean = 'and')
 * @method static Builder|Contact whereCity($value)
 * @method static Builder|Contact whereContact($value)
 * @method static Builder|Contact whereCreatedAt($value)
 * @method static Builder|Contact whereDeletedAt($value)
 * @method static Builder|Contact whereEmail($value)
 * @method static Builder|Contact whereEndsWith($column, $value, $boolean = 'and')
 * @method static Builder|Contact whereFax($value)
 * @method static Builder|Contact whereFullName($value)
 * @method static Builder|Contact whereId($value)
 * @method static Builder|Contact whereLike($column, $value, $boolean = 'and')
 * @method static Builder|Contact wherePhone($value)
 * @method static Builder|Contact whereState($value)
 * @method static Builder|Contact whereUpdatedAt($value)
 * @method static Builder|Contact whereUserId($value)
 * @method static Builder|Contact whereZip($value)
 * @mixin Eloquent
 *
 * @method static ContactFactory factory(...$parameters)
 */
class Contact extends Model
{
    use Filterable;
    use SoftDeletes;
    use Notifiable;
    use SetCompanyId;
    use HasFactory;

    public const CONTACT_TYPE_PRIVATE = 7;

    public const CONTACT_TYPES = [
        1 => 'Other',
        2 => 'Auction',
        3 => 'Business',
        4 => 'Broker',
        5 => 'Car rental',
        6 => 'Dealership',
        self::CONTACT_TYPE_PRIVATE => 'Private',
        8 => 'Repo/Towing',
        9 => 'Warehouse',
    ];

    public const TABLE_NAME = 'contacts';

    /**
     * @var array
     */
    protected $fillable = [
        'full_name',
        'address',
        'city',
        'state_id',
        'zip',
        'phone',
        'phone_extension',
        'phone_name',
        'phones',
        'email',
        'fax',
        'comment',
        'timezone',
        'type_id',
        'working_hours',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'phones' => 'array',
        'working_hours' => 'array',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(
            'hidden',
            function (Builder $builder) {
                $builder->where(
                    [
                        ['full_name', '!=', ''],
                        'hidden' => false,
                    ]
                );
            }
        );

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    /**
     * @return string
     */
    public function modelFilter()
    {
        return $this->provideFilter(ContactFilter::class);
    }

    public function setUser(User $user): self
    {
        $this->user_id = $user->id;
        return $this;
    }

    public function state(): HasOne
    {
        return $this->hasOne(State::class, 'id', 'state_id');
    }

    public function getState(): ?State
    {
        return $this->state_id
            ? $this->getStateRepository()->findById($this->state_id)
            : null;
    }

    protected function getStateRepository(): StateRepository
    {
        return resolve(StateRepository::class);
    }

    public function scopeFindByPhone(Builder $builder, string $phone): void
    {
        $builder
            ->where('phone', $phone)
            ->orWhereJsonContains('phones->phone', $phone);
    }
}
