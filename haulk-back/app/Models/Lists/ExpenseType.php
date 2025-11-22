<?php

namespace App\Models\Lists;

use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ExpenseType extends Model
{
    use HasFactory;
    use SetCompanyId;

    public const TABLE_NAME = 'expense_types';

    protected $fillable = [
        'title',
    ];

    public const EXPENSE_TYPES = [
        1 => 'Pilot / Flying J',
        2 => 'Love\'s',
        3 => 'Logbook / week',
        4 => 'Other',
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
        return collect(self::EXPENSE_TYPES)
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
