<?php

namespace App\Models\Employees;

use App\Casts\EmailCast;
use App\Enums\Employees;
use App\Filters\Employees\EmployeeFilter;
use App\Models\BaseAuthenticatable;
use App\Models\Departments\Department;
use App\Models\ListPermission;
use App\Models\Reports\Report;
use App\Models\Sips\Sip;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\LoginDataTrait;
use App\Traits\Permissions\DefaultListPermissionTrait;
use App\Traits\Permissions\HasRoles;
use App\Traits\SetPasswordTrait;
use App\ValueObjects\Email;
use Carbon\Carbon;
use Database\Factories\Employees\EmployeeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int id
 * @property string guid
 * @property null|Employees\Status status
 * @property string first_name
 * @property string last_name
 * @property Email email
 * @property null|string email_verification_code
 * @property null|Carbon email_verified_at
 * @property string password
 * @property int department_id
 * @property null|int sip_id
 * @property string lang
 * @property null|Carbon deleted_at
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property boolean is_insert_kamailio     // загружены данные в бд kamailio
 * @property boolean is_insert_queue        // добавлен пользователь к очереди (asterisk)
 *
 * @see Employee::department()
 * @property-read Department department
 *
 * @see Employee::sip()
 * @property-read Sip sip
 *
 * @see Employee::report()
 * @property-read Report report
 *
 * @method static EmployeeFactory factory(int $number = null)
 */
class Employee extends BaseAuthenticatable implements ListPermission
{
    use HasFactory;
    use Filterable;
    use SetPasswordTrait;
    use DefaultListPermissionTrait;
    use HasRoles;
    use SoftDeletes;
    use LoginDataTrait;

    public const MORPH_NAME = 'employee';

    protected $table = self::TABLE;
    public const TABLE = 'employees';

    public const GUARD = 'graph_employee';

    protected $fillable = [
        'is_insert_kamailio',
        'is_insert_queue',
        'sip_id',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email' => EmailCast::class,
        'status' => Employees\Status::class,
        'is_insert_kamailio' => 'boolean',
        'is_insert_queue' => 'boolean',
    ];

    public function hasSubscriberRecord(): bool
    {
        return $this->is_insert_kamailio;
    }

    public function hasQueueMemberRecord(): bool
    {
        return $this->is_insert_queue;
    }

    public function modelFilter(): string
    {
        return EmployeeFilter::class;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function sip(): BelongsTo
    {
        return $this->belongsTo(Sip::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(Report::class);
    }

    public function getName(): string
    {
        $fullName = sprintf('%s %s', $this->first_name, $this->last_name);

        return str_replace(' ', ' ', $fullName);
    }

    public function getEmailVerificationCode(): ?string
    {
        return $this->email_verification_code;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}
