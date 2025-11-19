<?php

declare(strict_types=1);

use Carbon\Carbon;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Wezom\Core\Entities\SelectOption;
use Wezom\Core\Enums\Formats\DatetimeEnum;
use Wezom\Core\Models\Language;
use Wezom\Core\Services\Database\TransactionService;

if (!function_exists('date_interval_to_seconds')) {
    function date_interval_to_seconds(DateInterval $interval): int
    {
        return (int)($interval->days * 86400 + $interval->h * 3600
            + $interval->i * 60 + $interval->s + round($interval->f));
    }
}

if (!function_exists('json_to_array')) {
    function json_to_array(?string $jsonString = ''): array
    {
        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }
}

if (!function_exists('array_to_json')) {
    function array_to_json(array $array, $options = 0): string
    {
        return json_encode($array, JSON_THROW_ON_ERROR | $options);
    }
}

if (!function_exists('is_testing')) {
    #[\JetBrains\PhpStorm\Pure] function is_testing(): bool
    {
        return config('app.env') === 'testing';
    }
}

if (!function_exists('is_prod')) {
    #[\JetBrains\PhpStorm\Pure] function is_prod(): bool
    {
        return config('app.env') === 'production';
    }
}

if (!function_exists('yes_or_no')) {
    function yes_or_no(mixed $value): string
    {
        return (bool)$value ? 'Yes' : 'No';
    }
}

if (!function_exists('trim_ds')) {
    function trim_ds(string $value): string
    {
        return trim($value, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('to_model_key')) {
    function to_model_key(Model|int|string $model): int|string
    {
        return $model instanceof Model
            ? $model->getKey()
            : $model;
    }
}

if (!function_exists('make_transaction')) {
    /**
     * @param  array<Connection>  $connections
     *
     * @throws Throwable
     */
    function make_transaction(Closure $action, array $connections = []): mixed
    {
        /** @var TransactionService $service */
        $service = app(TransactionService::class);

        return $service->handle($action, $connections);
    }
}

if (!function_exists('get_current_running_time')) {
    function get_current_running_time(): float
    {
        return microtime(true) - LARAVEL_START;
    }
}

if (!function_exists('mi_to_km')) {
    function mi_to_km(float $mi): float
    {
        return $mi * 1.60934;
    }
}

if (!function_exists('null_or_int')) {
    function null_or_int(null|int|string $value): ?int
    {
        return !is_null($value) ? (int)$value : null;
    }
}

if (!function_exists('prepare_datetime_for_db')) {
    function prepare_datetime_for_db(?string $datetime): ?string
    {
        if (empty($datetime)) {
            return null;
        }

        return Carbon::createFromFormat(config('app.datetime_format'), $datetime)
            ->format(DatetimeEnum::DEFAULT_FORMAT->value);
    }
}

if (!function_exists('prepare_date_for_db')) {
    function prepare_date_for_db(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        return Carbon::createFromFormat(config('app.date_format'), $date)
            ->format(DatetimeEnum::DATE->value);
    }
}

if (!function_exists('media_hash_file')) {
    function media_hash_file(string $filename, string $extension): string
    {
        return md5($filename . microtime()) . '.' . $extension;
    }
}

if (!function_exists('get_imports_folder')) {
    function get_imports_folder(string $filename): string
    {
        return Storage::disk('imports')->path($filename);
    }
}

if (!function_exists('enum_formatted')) {
    function enum_formatted(string $enum): string
    {
        return Str::title(Str::replace('_', ' ', $enum));
    }
}

if (!function_exists('enum_to_string')) {
    function enum_to_string(\BackedEnum|\UnitEnum|string|null $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return $value;
    }
}

if (!function_exists('languages')) {
    /**
     * @return Collection<Language>
     */
    function languages(): Collection
    {
        return app('localization')->getAllLanguages();
    }
}

if (!function_exists('core_version')) {
    function core_version(): string
    {
        return Cache::rememberForever('core-version', function () {
            $composer = json_decode(file_get_contents(dirname(__FILE__, 2) . '/composer.json'), true);

            return $composer['version'];
        });
    }
}

if (!function_exists('active_scope')) {
    /**
     * Apply active scope.
     *
     * @return callable
     */
    function active_scope()
    {
        return function ($query) {
            /** @var \Wezom\Core\Traits\Model\ActiveScopeTrait $query */
            /** @phpstan-ignore-next-line  */
            $query->active();
        };
    }
}

if (!function_exists('has_root_directive')) {
    /**
     * Check if root operation has directive.
     */
    function has_root_directive(ResolveInfo $resolveInfo, string $directiveName): bool
    {
        $rootPathName = $resolveInfo->path[0];

        $operationType = $resolveInfo->schema->getOperationType($resolveInfo->operation->operation);

        // If not found operation field - try search by alias
        if ($operationType->findField($rootPathName) == null) {
            foreach ($resolveInfo->operation->selectionSet->selections as $selection) {
                /** @phpstan-ignore-next-line  */
                if ($selection->alias?->value === $rootPathName) {
                    /** @phpstan-ignore-next-line  */
                    $rootPathName = $selection->name->value;
                    break;
                }
            }
        }

        $operationField = $operationType->getField($rootPathName);

        foreach ($operationField->astNode->directives as $directive) {
            if ($directive->name->value == $directiveName) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('map_with_keys_and_sort')) {
    function map_with_keys_and_sort(?array $ids, string $pivot = 'sort'): array
    {
        if (empty($ids)) {
            return [];
        }

        return collect($ids)
            ->mapWithKeys(
                static fn (int $id, int $index) => [
                    $id => [
                        $pivot => $index,
                    ],
                ]
            )->toArray();
    }
}

if (!function_exists('escape_like')) {
    /**
     * Escape special chars for DB LIKE expression.
     */
    function escape_like(?string $value, bool $addPercents = true): string
    {
        $value = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);

        return $addPercents ? "%$value%" : $value;
    }
}

if (!function_exists('build_select_options_list')) {
    /**
     * @return array<int, SelectOption>
     */
    function build_select_options_list(
        iterable $items,
        array $disabledIds = [],
        string $parentKey = 'parent_id',
        string $idField = 'id',
        string $nameField = 'name'
    ): array {
        return build_select_options_tree(
            group_by_parent_id($items, $parentKey),
            disabledIds: $disabledIds,
            idField: $idField,
            nameField: $nameField
        );
    }
}

if (!function_exists('build_select_options_tree')) {
    /**
     * <p>
     *     Call this function with grouped by parent_id items:
     *     <pre>
     *     build_select_options_tree(
     *         group_by_parent_id($items)
     *     )
     *     </pre>
     * </p>
     *
     * <p>
     *     Or call directly build_select_options_list function:
     *     <pre>
     *         build_select_options_list($items)
     *     </pre>
     * </p>
     *
     * @return array<int, SelectOption>
     *
     * @see group_by_parent_id()
     * @see build_select_options_list()
     */
    function build_select_options_tree(
        array $tree,
        ?int $id = null,
        array $disabledIds = [],
        array &$result = [],
        int $depth = 0,
        string $idField = 'id',
        string $nameField = 'name'
    ): array {
        foreach ($tree[$id] ?? [] as $item) {
            $itemId = $item->{$idField};
            $itemName = $item->{$nameField};

            $hasChildren = isset($tree[$itemId]);

            $result[] = new SelectOption($itemId, $itemName, $hasChildren, in_array($itemId, $disabledIds), $depth);

            if ($hasChildren) {
                build_select_options_tree($tree, $itemId, $disabledIds, $result, $depth + 1, $idField, $nameField);
            }
        }

        return $result;
    }
}

if (!function_exists('group_by_parent_id')) {
    function group_by_parent_id(iterable $items, string $parentKey = 'parent_id'): array
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

if (!function_exists('get_all_children')) {
    function get_all_children(iterable $list, $id, &$result = [], $parentKey = 'parent_id'): array
    {
        if (!$id) {
            return $result;
        }
        foreach ($list as $obj) {
            if (is_array($obj)) {
                if ($obj[$parentKey] == $id) {
                    $result[] = $obj['id'];
                    get_all_children($list, $obj['id'], $result, $parentKey);
                }
            } else {
                if ($obj->{$parentKey} == $id) {
                    $result[] = $obj->id;
                    get_all_children($list, $obj->id, $result, $parentKey);
                }
            }
        }

        return $result;
    }
}

if (!function_exists('get_all_parents')) {
    function get_all_parents(iterable $list, ?int $id = null, array &$result = [], string $parentKey = 'id'): array
    {
        if (!$id) {
            return $result;
        }
        foreach ($list as $obj) {
            if (is_array($obj)) {
                if ($obj[$parentKey] == $id) {
                    $result[] = $obj['id'];
                    get_all_parents($list, $obj['parent_id'], $result, $parentKey);
                }
            } else {
                if ($obj->{$parentKey} == $id) {
                    $result[] = $obj->id;
                    get_all_parents($list, $obj->parent_id, $result, $parentKey);
                }
            }
        }

        return $result;
    }
}

if (!function_exists('get_root_parent_id')) {
    function get_root_parent_id(iterable $list, int $id, string $parentKey = 'parent_id', string $key = 'id'): ?int
    {
        foreach ($list as $obj) {
            $itemId = is_array($obj) ? $obj[$key] : $obj->{$key};
            if ($itemId == $id) {
                $parentId = is_array($obj) ? $obj[$parentKey] : $obj->{$parentKey};
                if (!$parentId) {
                    return $id;
                }

                return get_root_parent_id($list, $parentId, $parentKey, $key);
            }
        }

        return null;
    }
}

if (!function_exists('map_with_keys_and_sort')) {
    function map_with_keys_and_sort(?array $ids, string $pivot = 'sort'): array
    {
        if (empty($ids)) {
            return [];
        }

        return collect($ids)
            ->mapWithKeys(
                static fn (int $id, int $index) => [
                    $id => [
                        $pivot => $index
                    ]
                ]
            )->toArray();
    }
}

if (!function_exists('url_to_admin')) {
    function url_to_admin(string $url): string
    {
        return rtrim(config('app.admin_url'), '/') . '/' . ltrim($url, '/');
    }
}

if (!function_exists('url_to_site')) {
    function url_to_site(string $url): string
    {
        return rtrim(config('app.front_url'), '/') . '/' . ltrim($url, '/');
    }
}

if (!function_exists('convert_meters_to_miles')) {

    function convert_meters_to_miles(float $meters): float
    {
        return round($meters * 0.000621371, 2);
    }
}

if (!function_exists('logger_info')) {

    function logger_info($message, array $context = [])
    {
        if (config('logging.channels.eyes.enable')) {
            Illuminate\Support\Facades\Log::channel('eyes')->info($message, $context);
        }
    }
}
