<?php

namespace App\Traits\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

trait JoinColumnsTrait
{
    protected static array $DEFAULT_IGNORED_JOIN_COLUMNS = [
        'id',
        'created_at',
        'updated_at'
    ];

    public static function joinColumns(): string
    {
        return Cache::rememberForever(
            static::TABLE_NAME . '_join_columns',
            static fn(): string => collect(Schema::getColumnListing(static::TABLE_NAME))
                ->filter(
                    fn(string $item) => !in_array(
                        $item,
                        static::$IGNORED_JOIN_COLUMNS ?? self::$DEFAULT_IGNORED_JOIN_COLUMNS
                    )
                )
                ->map(
                    fn(string $item) => static::TABLE_NAME . '.' . $item
                )
                ->implode(', ')
        );
    }
}
