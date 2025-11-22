<?php

namespace App\Models\Lists;

use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BonusType extends Model
{
    use HasFactory;
    use SetCompanyId;

    public const TABLE_NAME = 'bonus_types';

    protected $fillable = [
        'title',
    ];

    public const BONUS_TYPES = [
        1 => 'Bonus',
        2 => 'Other',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    public static function getDefaultTypesList(): array
    {
        return collect(self::BONUS_TYPES)
            ->map(
                function ($item, $key) {
                    return [
                        'id' => $key,
                        'title' => $item,
                    ];
                }
            )->all();
    }
}
