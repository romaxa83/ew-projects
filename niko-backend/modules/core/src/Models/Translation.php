<?php

namespace WezomCms\Core\Models;

use Event;
use Illuminate\Database\Eloquent\Model;
use Lang;
use WezomCms\Core\Contracts\TranslationStorageInterface;
use WezomCms\Core\Enums\TranslationSide;
use Yadakhov\InsertOnDuplicateKey;

/**
 * \WezomCms\Core\Models\Translation
 *
 * @property int $id
 * @property string|null $namespace
 * @property string $side
 * @property string $key
 * @property string $locale
 * @property string|null $text
 * @property int|null $translated
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_key
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereNamespace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereSide($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereTranslated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Translation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Translation extends Model
{
    const API_NAMESPACE = 'api';
    const SIDE_SITE = 'site';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['namespace', 'side', 'key', 'locale', 'text'];

    /**
     * @param  string  $side
     * @param  array  $locales
     * @return array
     */
    public static function getTranslations(string $side, array $locales)
    {
        $result = [];

        self::whereSide($side)
            ->whereIn('locale', $locales)
            ->get()
            ->each(function (self $translation) use (&$result) {
                $result[$translation->full_key][$translation->locale] = [
                    'id' => $translation->id,
                    'text' => $translation->text,
                ];
            });

        return $result;
    }

    /**
     * @param $key
     */
    public static function saveNewKey(string $key)
    {
        $parts = static::parseKey($key);
        switch ($parts['side']) {
            case TranslationSide::ADMIN:
                foreach (config('cms.core.translations.admin.locales', []) as $locale => $language) {
                    $parts['locale'] = $locale;

                    static::firstOrCreate($parts, ['text' => static::getDefaultText($locale, $parts, $key)]);
                }
                break;
            case TranslationSide::SITE:
                foreach (app('locales') as $locale => $language) {
                    $parts['locale'] = $locale;

                    static::firstOrCreate($parts, ['text' => static::getDefaultText($locale, $parts, $key)]);
                }
                break;
        }
    }

    /**
     * @param $key
     * @return array
     */
    public static function parseKey(string $key)
    {
        $result = [];

        $parts = explode('::', $key, 2);
        if (count($parts) === 2) {
            $result['namespace'] = $parts[0];
            $keyParts = explode('.', array_get($parts, 1, ''), 2);
        } else {
            $result['namespace'] = TranslationStorageInterface::GLOBAL_NS;
            $keyParts = explode('.', $key, 2);
        }

        $result['side'] = array_get($keyParts, 0);
        $result['key'] = array_get($keyParts, 1);

        return $result;
    }

    /**
     * @param  string  $locale
     * @param  array  $parts
     * @param  string  $key
     * @return string
     */
    public static function getDefaultText(string $locale, array $parts, string $key): string
    {
        $defaultKey = implode(
            '',
            [
                array_get($parts, 'namespace') . '::',
                'default.',
                array_get($parts, 'side') . '.',
                array_get($parts, 'key')
            ]
        );

        if (Lang::has($defaultKey, $locale)) {
            return Lang::get($defaultKey, [], $locale);
        }

        return static::extractTextFromKey($key);
    }

    /**
     * @param  string  $key
     * @return string
     */
    private static function extractTextFromKey(string $key): string
    {
        if (preg_match('/^[\w_-]+?::[a-zA-Z_\.-]+\.(.*)$/', $key, $matches)) {
            return array_get($matches, 1, '');
        }

        return '';
    }

    /**
     * @return string
     */
    public function getFullKeyAttribute()
    {
        $pieces = [];
        if ($this->namespace) {
            $pieces[] = $this->namespace . '::';
        }
        if ($this->side) {
            $pieces[] = $this->side . '.';
        }
        $pieces[] = $this->key;

        return implode('', $pieces);
    }

    /**
     * @param  string|array|mixed  $path
     */
    public static function addScannerDir($path)
    {
        $path = is_array($path) ? $path : func_get_args();

        Event::listen('translator:view_directories', function () use ($path) {
            return $path;
        });
    }

    /**
     * @param  string|array|mixed  $keys
     */
    public static function addScannerKeys($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        Event::listen('translator:find_keys', function () use ($keys) {
            return $keys;
        });
    }
}
