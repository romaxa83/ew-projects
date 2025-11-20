<?php

namespace WezomCms\Cars\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property int $engine_type_id
 * @property string $locale
 * @property string|null $name
 * @mixin \Eloquent
 */
class EngineTypeTranslation extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'car_engine_type_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
