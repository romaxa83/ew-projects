<?php

namespace App\Models\Settings;

use App\Models\Files\HasMedia;
use App\Models\Files\SettingImage;
use App\Models\Files\Traits\HasMediaTrait;
use Illuminate\Support\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Settings\Setting
 *
 * @property int $id
 * @property string $group
 * @property string $alias
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Setting newModelQuery()
 * @method static Builder|Setting newQuery()
 * @method static Builder|Setting query()
 * @method static Builder|Setting whereAlias($value)
 * @method static Builder|Setting whereCreatedAt($value)
 * @method static Builder|Setting whereGroup($value)
 * @method static Builder|Setting whereId($value)
 * @method static Builder|Setting whereUpdatedAt($value)
 * @method static Builder|Setting whereValue($value)
 *
 * @mixin Eloquent
 */
class Setting extends Model implements HasMedia
{
    use HasMediaTrait;

    public const TABLE_NAME = 'settings';
    public const GROUP_CARRIER = 'carrier';
    public const LOGO_FIELD_CARRIER = 'logo';
    public const W9_FIELD_CARRIER = 'w9_form_image';
    public const USDOT_FIELD_CARRIER = 'usdot_number_image';
    public const INSURANCE_FIELD_CARRIER = 'insurance_certificate_image';

    public const COMPANY_SETTINGS_NOTIFICATION = 'company_settings_notification';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'group',
        'alias',
        'value',
    ];

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getImageClass(): string
    {
        return SettingImage::class;
    }
}
