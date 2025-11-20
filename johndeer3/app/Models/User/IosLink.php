<?php

namespace App\Models\User;

use App\ModelFilters\User\IosLinkFilter;
use App\Models\BaseModel;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;

/**
 *
 * @property int $id
 * @property string $code
 * @property string $link
 * @property boolean $status
 * @property integer|null $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User|null $user
 */

class IosLink extends BaseModel
{
    use HasFactory;
    use Filterable;

    public $table = 'ios_links';

    public $fillable = [
        'code',
        'link',
        'status',
        'user_id'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(IosLinkFilter::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public static function createFromImport(array $data): void
    {
        $model = new self;
        $model->code = Arr::get($data, 'code', null);
        $model->link = Arr::get($data, 'link', null);
        $model->save();
    }
}
