<?php

namespace WezomCms\Catalog\Models;

/**
 * Class CatalogSeoTemplateSpecification
 * @property int $catalog_seo_template_id
 * @property string $parameter
 */
class CatalogSeoTemplateParameter extends \Illuminate\Database\Eloquent\Model
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
    protected $fillable = ['parameter'];
}
