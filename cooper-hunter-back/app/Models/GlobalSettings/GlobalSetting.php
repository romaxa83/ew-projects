<?php

namespace App\Models\GlobalSettings;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\GlobalSettings\GlobalSettingFactory;

/**
 * @property string footer_address
 * @property string footer_email
 * @property string footer_phone
 * @property string footer_instagram_link
 * @property string footer_meta_link
 * @property string footer_twitter_link
 * @property string footer_youtube_link
 * @property string footer_additional_email
 * @property string footer_google_pay_link
 * @property int slider_countdown
 * @property string|null company_site
 * @property string|null company_company
 *
 * @method static GlobalSettingFactory factory()
 */
class GlobalSetting extends BaseModel
{
    use HasFactory;

    public const TABLE = 'global_settings';

    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'footer_address',
        'footer_email',
        'footer_phone',
        'footer_instagram_link',
        'footer_meta_link',
        'footer_twitter_link',
        'footer_youtube_link',
        'footer_additional_email',
        'footer_app_store_link',
        'footer_google_pay_link',
        'company_site',
        'company_title',
    ];
}
