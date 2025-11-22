<?php

namespace App\Models\Settings;

use App\Foundations\Modules\Media\Contracts\HasMedia;
use App\Foundations\Modules\Media\Images\SettingsImage;
use App\Foundations\Modules\Media\Traits\InteractsWithMedia;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property string value
 *
 * @mixin Eloquent
 */
class Settings extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const TABLE = 'settings';
    protected $table = self::TABLE;

    public const MORPH_NAME = 'settings';

    public const JSON_FIELDS = [
        'phones',
        'billing_phones',
        'ecommerce_phones',
        'ecommerce_billing_phones',
    ];

    public const LOGO_FIELD = 'logo';
    public const ECOMM_LOGO_FIELD = 'ecommerce_logo';

    public $timestamps = false;

    /**@var array <int, string>*/
    protected $fillable = [
        'name',
        'value',
    ];

    private static array $params = [];

    public function getImageClass(): string
    {
        return SettingsImage::class;
    }

    /**
     * @param string $paramName
     * @return string|array|null
     */
    public static function getParam(string $paramName)
    {
        if (isset(self::$params[$paramName])) {
            return self::$params[$paramName];
        }
        $param = self::query()->where('name', $paramName)->first();
        $value = $param->value ?? null;

        if ($value && in_array($paramName, self::JSON_FIELDS)) {
            $value = json_decode($value, true);
        }

        self::$params[$paramName] = $value;

        return $value;
    }
}
