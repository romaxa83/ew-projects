<?php

namespace Wezom\Quotes\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Wezom\Quotes\Enums\QuoteStatusEnum;

/**
 * @property int $id
 * @property int $quote_id
 * @property QuoteStatusEnum $prev_status
 * @property QuoteStatusEnum $new_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|QuoteHistoryStatus newModelQuery()
 * @method static Builder|QuoteHistoryStatus newQuery()
 * @method static Builder|QuoteHistoryStatus query()
 * @method static Builder|QuoteHistoryStatus whereCreatedAt($value)
 * @method static Builder|QuoteHistoryStatus whereId($value)
 * @mixin Eloquent
 */
class QuoteHistoryStatus extends Model
{
    public const TABLE = 'quote_history_statuses';

    protected $fillable = [
        'quote_id',
        'prev_status',
        'new_status',
    ];
    protected $casts = [
        'prev_status' => QuoteStatusEnum::class,
        'new_status' => QuoteStatusEnum::class,
    ];
}
