<?php

namespace WezomCms\Promotions\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property int $promotions_id
 * @property string $locale
 * @property string|null $name
 * @property string|null $text
 * @property string|null $link
 * @mixin \Eloquent
 */
class PromotionsTranslation extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'promotions_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'text', 'link'];
}
