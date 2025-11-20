<?php

namespace WezomCms\Cars\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\ExtendPackage\Translatable;

/**
 *
 * @property int $id
 * @mixin \Eloquent
 * @mixin EngineTypeTranslation
 */
class EngineType extends Model
{
    use Translatable;

    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'car_engine_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Names of the fields being translated in the "Translation" model.
     *
     * @var array
     */
    protected $translatedAttributes = ['name'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];
}




