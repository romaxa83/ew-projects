<?php

namespace App\Models\BodyShop\Settings;

use App\Models\Files\BodyShop\SettingsImage;
use App\Models\Files\HasMedia;
use App\Models\Files\Traits\HasMediaTrait;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BodyShop\Settings
 *
 * @property int $id
 * @property string $name
 * @property string $value
 *
 * @mixin Eloquent
 */
class Settings extends Model implements HasMedia
{
    use HasMediaTrait;

    public const TABLE_NAME = 'bs_settings';

    public const JSON_FIELDS = [
        'phones',
        'billing_phones',
    ];

    public const LOGO_FIELD = 'logo';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    /**
     * @var array
     */
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
