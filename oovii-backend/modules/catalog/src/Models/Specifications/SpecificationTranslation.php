<?php

namespace WezomCms\Catalog\Models\Specifications;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SpecificationTranslation
 * @property int $specification_id
 * @property string $locale
 * @property string $name
 */
class SpecificationTranslation extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
