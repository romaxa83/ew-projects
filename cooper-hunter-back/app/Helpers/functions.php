<?php

use App\Collections\Catalog\Categories\CategoryStorage;
use App\Models\BaseModel;
use App\Models\Localization\Language;
use App\Models\Localization\Translate;
use App\Services\Catalog\Categories\CategoryStorageService;
use Core\Services\Cache\LockerService;
use Core\Services\Database\TransactionService;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;

if (!function_exists('array_is_list_of_string')) {
    function array_is_list_of_string(array $array): bool
    {
        foreach ($array as $key => $item) {
            if (!is_int($key)) {
                return false;
            }

            if (!is_string($item)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('generatePassword')) {
    /**
     * @var int $sectionLength - Length of each section
     * @var bool $withSpecialChar - determine if special symbol should be added to password
     *
     * Password sections are:
     * - [1] lower case char
     * - [2] upper case char
     * - [3] digit
     * - [4] special symbol (only with $withSpecialChar flag)
     *
     * Generated length will be:
     * ([1] * $sectionLength) + ([2] * $sectionLength) + ([3] * $sectionLength) + ([4]?)
     */
    function generatePassword(int $sectionLength = 3, bool $withSpecialChar = false): string
    {
        $randomChar = static function (string $string, int $times = 1): string {
            $chars = '';

            foreach (range(0, max(0, $times - 1)) as $ignored) {
                try {
                    $chars .= $string[random_int(0, strlen($string) - 1)];
                } catch (Throwable) {
                    $chars .= str_shuffle($string)[0];
                }
            }

            return $chars;
        };

        //Without visually similar characters
        $special = '#%^&';
        $chars = 'ABCDEFGHKMNPRSTUVWXYZ';
        $digits = '2345678';

        $pwd = Str::upper($randomChar($chars, $sectionLength))
            . Str::lower($randomChar($chars, $sectionLength))
            . $randomChar($digits, $sectionLength);

        if ($withSpecialChar) {
            $pwd .= $randomChar($special);
        }

        return str_shuffle($pwd);
    }
}

if (!function_exists('dateIntervalToSeconds')) {
    function dateIntervalToSeconds(DateInterval $interval): int
    {
        return $interval->d * 86400 + $interval->h * 3600
            + $interval->i * 60 + $interval->s + round($interval->f);
    }
}

if (!function_exists('jsonToArray')) {
    function jsonToArray(?string $jsonString = ''): array
    {
        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }
}

if (!function_exists('arrayToJson')) {
    function arrayToJson(array $array, $options = 0): string
    {
        return json_encode($array, JSON_THROW_ON_ERROR | $options);
    }
}

if (!function_exists('isTesting')) {
    #[Pure] function isTesting(): bool
    {
        return config('app.env') === 'testing';
    }
}

if (!function_exists('isProd')) {
    #[Pure] function isProd(): bool
    {
        return config('app.env') === 'production';
    }
}

if (!function_exists('languages')) {
    /**
     * @return Collection|Language[]
     */
    function languages(): Collection|array
    {
        return app('localization')->getAllLanguages();
    }
}

if (!function_exists('_t')) {
    function _t(string $place, string $key, string $lang = null): string
    {
        $lang = $lang ?: app()->getLocale();

        return Translate::query()
            ->where(
                compact('place', 'key', 'lang')
            )
            ->first()
            ?->text ?: $key;
    }
}

if (!function_exists('defaultLanguage')) {
    function defaultLanguage(): Language
    {
        return app('localization')->getDefault();
    }
}

if (!function_exists('trimDS')) {
    function trimDS(string $value): string
    {
        return trim($value, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('locker')) {
    function locker(): LockerService
    {
        return app(LockerService::class);
    }
}

if (!function_exists('toModelKey')) {
    function toModelKey(BaseModel|int|string $model): int|string
    {
        return $model instanceof BaseModel
            ? $model->getKey()
            : $model;
    }
}

if (!function_exists('makeTransaction')) {
    /**
     * @param Closure $action
     * @param array<Connection> $connections
     * @return mixed
     * @throws Throwable
     */
    function makeTransaction(Closure $action, array $connections = []): mixed
    {
        return app(TransactionService::class)->handle($action, $connections);
    }
}

if (!function_exists('getCurrentRunningTime')) {
    function getCurrentRunningTime(): float
    {
        return (microtime(true) - LARAVEL_START);
    }
}

if (!function_exists('builderToSql')) {
    /**
     * @param \Illuminate\Database\Eloquent\Builder|Builder $builder
     * @return string
     */
    function builderToSql($builder): string
    {
        $sql = str_replace('?', '%s', $builder->toSql());

        return vsprintf($sql, $builder->getBindings());
    }
}

if (!function_exists('groupByParentId')) {
    function groupByParentId(iterable $items, string $parentKey = 'parent_id'): array
    {
        $result = [];
        foreach ($items as $item) {
            if (is_object($item)) {
                $result[$item->$parentKey][] = $item;
            } elseif (is_array($item)) {
                $result[$item[$parentKey]][] = $item;
            }
        }

        return $result;
    }
}

if (!function_exists('__config')) {
    function __config(string $key, array $replacements = []): ?string
    {
        $str = config($key);
        if (!is_string($str)) {
            return null;
        }

        foreach ($replacements as $k => $replacement) {
            $str = Str::replace(':' . $k, $replacement, $str);
        }

        return $str;
    }
}

if (!function_exists('categoryStorage')) {
    function categoryStorage(): CategoryStorage
    {
        return resolve(CategoryStorageService::class)->clear()->generate();
    }
}

if (!function_exists('makeSearchSlug')) {
    function makeSearchSlug(string $title): string
    {
        $title = str_replace('@', '', $title);

        return Str::slug($title, '');
    }
}

if (!function_exists('stdCollectionToArray')) {
    /**
     * @throws JsonException
     */
    function stdCollectionToArray(\Illuminate\Support\Collection $collection)
    {
        return json_decode(json_encode($collection->toArray(), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}

if (!function_exists('pretty_price')) {

    function pretty_price(float $price)
    {
        if($price){
            return (float)number_format($price, 2, '.', '');
        }
        return 0;
    }
}

if (!function_exists('clear_str')) {

    function clear_str(string $value): string
    {
        return str_replace(' ', '', mb_strtolower(trim($value)));
    }
}

if (!function_exists('logger_info')) {

    function logger_info($message, array $context = [])
    {
        Log::channel('eyes')->info($message, $context);
    }
}

if (!function_exists('randomState')) {
    function randomState(): string
    {
        //missed states in project are commented
        $states = [
//            "Alaska",
            "Alabama",
            "Arizona",
            "Arkansas",
//            "American Samoa",
            "California",
            "Colorado",
            "Connecticut",
            "Delaware",
            "District of Columbia",
            "Florida",
            "Georgia",
//            "Hawaii",
            "Idaho",
            "Illinois",
            "Indiana",
            "Iowa",
            "Kansas",
            "Kentucky",
            "Louisiana",
            "Maine",
            "Maryland",
            "Massachusetts",
            "Michigan",
            "Minnesota",
            "Mississippi",
            "Missouri",
            "Montana",
            "Nebraska",
            "Nevada",
            "New Hampshire",
            "New Jersey",
            "New Mexico",
            "New York",
            "North Carolina",
            "North Dakota",
            "Ohio",
            "Oklahoma",
            "Oregon",
            "Pennsylvania",
//            "Puerto Rico",
            "Rhode Island",
            "South Carolina",
            "South Dakota",
            "Tennessee",
            "Texas",
            "Utah",
            "Vermont",
            "Virginia",
//            "Virgin Islands",
            "Washington State",
            "West Virginia",
            "Wisconsin",
            "Wyoming"
        ];

        return $states[array_rand($states)];
    }
}
if (!function_exists('rule_in')) {

    function rule_in(...$values): ?string
    {
        if(empty($values)){
            return null;
        }
        $str = "in:";
        foreach ($values as $value){
            $str .= "{$value},";
        }

        return substr($str,0,-1);
    }
}

