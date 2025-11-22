<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;
use App\Models\Dealership\Department;
use App\Models\Media\Image;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property int $id
 * @property string $type
 * @property int $sort
 * @property bool $active
 * @property string|null $link
 * @property int $department_id
 * @property Carbon start_at
 * @property Carbon finish_at
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class Promotion extends BaseModel
{
    use HasFactory;

    public const TYPE_COMMON      = 'common';
    public const TYPE_INDIVIDUAL  = 'individual';

    public const TABLE = 'promotions';

    protected $table = self::TABLE;

    protected $dates = [
        'start_at',
        'finish_at',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static function statusList()
    {
        return [
            self::TYPE_COMMON => __('translation.promotions.type.common'),
            self::TYPE_INDIVIDUAL => __('translation.promotions.type.individual'),
        ];
    }

    public static function assertStatus($status): void
    {
        if(!array_key_exists($status, self::statusList())){
            throw new \InvalidArgumentException(__('error.not valid message status', ['status' => $status]));
        }
    }

    public static function checkStatus($status): bool
    {
        return array_key_exists($status, self::statusList());
    }

    public function isCommon(): bool
    {
        return $this->type === self::TYPE_COMMON;
    }

    public function isIndividual(): bool
    {
        return $this->type === self::TYPE_INDIVIDUAL;
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class,'department_id', 'id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PromotionTranslation::class, 'model_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(PromotionTranslation::class,'model_id', 'id')
            ->where('lang', \App::getLocale());
    }

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'entity');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'promotion_user_relations',
            'promotion_id', 'user_id'
        );
    }
}
