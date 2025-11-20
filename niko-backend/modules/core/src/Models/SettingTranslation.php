<?php

namespace WezomCms\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Core\Models\SettingTranslation
 *
 * @property int $id
 * @property int $setting_id
 * @property mixed $value
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\SettingTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\SettingTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\SettingTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\SettingTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\SettingTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\SettingTranslation whereSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\SettingTranslation whereValue($value)
 * @mixin \Eloquent
 */
class SettingTranslation extends Model
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
    protected $fillable = ['value'];

    /**
     * @param  mixed  $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        $array = json_decode($value, true, 512, JSON_BIGINT_AS_STRING);

        return JSON_ERROR_NONE === json_last_error() ? $array : $value;
    }

    /**
     * @param  mixed  $value
     */
    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['value'] = $value;
    }
}
