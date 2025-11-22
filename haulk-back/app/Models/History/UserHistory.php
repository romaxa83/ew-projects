<?php

namespace App\Models\History;

use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory;
    use SetCompanyId;

    public const TABLE_NAME = 'user_histories';

    public const STATUS_CREATED = 'Created';
    public const STATUS_DELETED = 'Deleted';
    public const STATUS_ACTIVATED = 'Activated';
    public const STATUS_DEACTIVATED = 'Deactivated';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }
}
