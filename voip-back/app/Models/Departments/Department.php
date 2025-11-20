<?php

namespace App\Models\Departments;

use App\Filters\Departments\DepartmentFilter;
use App\Models\BaseModel;
use App\Models\Employees\Employee;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\ActiveTrait;
use Carbon\Carbon;
use Database\Factories\Departments\DepartmentFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property string guid
 * @property string name
 * @property int sort
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property boolean is_insert_asterisk     // загружены данные в бд asterisk
 * @property int num                        // поле для asterisk, на который можно звонить в очередь
 * @property bool active
 *
 * @see Department::employees()
 * @property-read Collection|Employee[] employees
 *
 * @see Department::employeesCount()
 * @property-read int employeesCount
 *
 * @method static DepartmentFactory factory(int $number = null)
 */
class Department extends BaseModel
{
    use HasFactory;
    use Filterable;
    use ActiveTrait;

    protected $table = self::TABLE;
    public const TABLE = 'departments';

    protected $fillable = [
        'name',
        'is_insert_asterisk'
    ];

    protected $casts = [
        'is_insert_asterisk' => 'boolean',
        'active' => 'boolean',
    ];

    public function hasQueueRecord(): bool
    {
        return $this->is_insert_asterisk;
    }

    public function modelFilter(): string
    {
        return DepartmentFilter::class;
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function employeesCount(): int
    {
        return $this->employees()->count();
    }
}
