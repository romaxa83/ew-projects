<?php

namespace App\Models\Support;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property bool $active
 * @property int $sort
 *
 */
class Category extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'support_categories';

    protected $table = self::TABLE;

    protected $casts = [
        'active' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class, 'category_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(CategoryTranslation::class,'category_id', 'id')
            ->where('lang', \App::getLocale());
    }
}

