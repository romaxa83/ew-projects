<?php

namespace WezomCms\Regions\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property int $region_id
 * @property string $locale
 * @property string|null $name
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Menu\Models\MenuTranslation whereUrl($value)
 * @mixin \Eloquent
 */
class RegionTranslation extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	protected $table = 'region_translations';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name'];
}

