<?php

namespace App\Models\Users;

use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Illuminate\Database\Eloquent\Model;

class ChangeEmail extends Model
{
    use SetCompanyId;

    protected $fillable = [
        'new_email',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
