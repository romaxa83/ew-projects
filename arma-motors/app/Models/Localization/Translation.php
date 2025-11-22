<?php

namespace App\Models\Localization;

use App\Models\BaseModel;
use App\Traits\QueryCacheable;
use Carbon\Carbon;
use Eloquent;

/**
 * App\Models\Language
 *
 * @property int $id
 * @property string $place
 * @property string $key
 * @property string $text
 * @property string $lang
 * @property string|null $group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Eloquent
 */
class Translation extends BaseModel
{
    const PLACE_ADMIN = 'admin';        // переводы для админ-панели
    const PLACE_APP = 'app';            // переводы для мп
    const PLACE_UI_KIT = 'ui-kit';      // переводы для dev-разработке
    const PLACE_SYSTEM = 'system';      // переводы системные

//    use QueryCacheable;

    public const TABLE_NAME = 'translates';

    protected $table = self::TABLE_NAME;

    public function importGroup(): array
    {
        return config('translates.import.group');
    }

    public function isSystem()
    {
        return in_array($this->group, $this->importGroup());
//        return $this->place == self::PLACE_SYSTEM && in_array($this->group, $this->importGroup());
    }

    public static function listPLace(): array
    {
        return [
            self::PLACE_ADMIN => self::PLACE_ADMIN,
            self::PLACE_APP => self::PLACE_APP,
            self::PLACE_UI_KIT => self::PLACE_UI_KIT,
            self::PLACE_SYSTEM => self::PLACE_SYSTEM,
        ];
    }

    public static function checkPLace(?string $place): bool
    {
        return array_key_exists($place, self::listPLace());
    }

    public static function assetPLace(string $place): void
    {
        if(!self::checkPLace($place)){
            throw new \InvalidArgumentException(__('error.translations.not defined place', ['place' => $place]));
        }
    }
}

