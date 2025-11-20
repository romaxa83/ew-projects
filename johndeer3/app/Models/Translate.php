<?php

namespace App\Models;

use App\Repositories\LanguageRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int $id
 * @property string $model
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string $text
 * @property string $lang
 * @property string|null $alias
 * @property string|null $group
 *
 * @method static Translate|Builder ofTranslatedGroup($group)
 * @method static Translate|Builder orderByGroupKeys($ordered)
 */

class Translate extends Model
{
    use HasFactory;

    const LANG_RU = 'ru';
    const LANG_EN = 'en';
    const LANG_UA = 'ua';

    const TYPE_ROLE = 'role';
    const TYPE_DISCLAIMER = 'disclaimer';
    const TYPE_SITE = 'site';

    const SEPARATOR_SYS_ALIAS = '::';

    const GROUP_EXCEL = 'excel';
    const GROUP_AUTH = 'auth';
    const GROUP_VALIDATION = 'validation';
    const GROUP_TRANSLATES = 'translates';
    const GROUP_MESSAGE = 'message';
    const GROUP_PAGINATION = 'pagination';

    const TABLE = 'translates';
    protected $table = self::TABLE;

    public function isSysAlias(): bool
    {
        return (bool)strstr($this->alias, self::SEPARATOR_SYS_ALIAS);
    }

    public static function listAliasesForPdfFile(): array
    {
        return [
            'product_specialist',
            'account_name',
            'email',
            'country',
            'phone',
            'dealer',
            'dealer_id',
            'salesman_name',
            'customer',
            'company_name',
            'last_name',
            'first_name',
            'product_name',
            'customer_type',
            'potencial',
            'competitor',
            'product',
            'equipment_group',
            'model_description',
            'machine_serial_number',
            'manufacturer',
            'header_brand',
            'header_model',
            'serial_number_header',
            'images',
            'whatb',
            'whate',
            'eotf',
            'mam',
            'images_others',
            'video',
            'demo_assigment',
            'demo_resultes',
            'client_comment',
            'signature',
            'location',
            'values',
            'units',
            'download_link',
            'quantity',
            'trailed_equipment_type',
            'independent_equipment',
            'machine_with_trailer',
            'for machine',
            'trailer_model',
            'main_machines',
            'field_condition',
            'model_description.type',
            'model_description.size',
            'model_description.size_parameters',
            'disclaimer_title',
        ];
    }

    public static function getLanguage(): array
    {
        return app(LanguageRepository::class)->getForSelect();
    }

    public static function defaultLang(): ?string
    {
        return app(LanguageRepository::class)->getDefault()->slug ?? null;
    }

    public static function checkLanguage($lang): bool
    {
        return array_key_exists($lang, self::getLanguage());
    }

    public static function assetLanguage($lang): void
    {
        if(!self::checkLanguage($lang)){
            throw new \Exception(__('message.language_not_exists',['lang' => $lang]));
        }
    }

    public function entity()
    {
        return $this->morphTo();
    }

    public function scopeOfTranslatedGroup(Builder $query, string $group)
    {
        return $query->where('group', $group)->whereNotNull('text');
    }

    public function scopeOrderByGroupKeys(Builder $query, bool $ordered)
    {
        if ($ordered) {
            $query->orderBy('group')->orderBy('alias');
        }
        return $query;
    }
}
