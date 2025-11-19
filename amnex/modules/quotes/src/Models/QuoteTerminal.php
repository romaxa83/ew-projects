<?php

namespace Wezom\Quotes\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wezom\Quotes\Enums\QuoteTerminalTypeEnum;

/**
 * @property int $id
 * @property int $quote_id
 * @property int $terminal_id
 * @property QuoteTerminalTypeEnum $type
 * @method static Builder|QuoteTerminal newModelQuery()
 * @method static Builder|QuoteTerminal newQuery()
 * @method static Builder|QuoteTerminal query()
 * @method static Builder|QuoteTerminal whereCreatedAt($value)
 * @method static Builder|QuoteTerminal whereId($value)
 * @mixin Eloquent
 */
class QuoteTerminal extends Model
{
    use HasFactory;

    public const TABLE = 'quote_terminals';

    protected $fillable = [];
    protected $casts = [
        'type' => QuoteTerminalTypeEnum::class,
    ];
}
