<?php

namespace Wezom\Quotes\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $address
 * @property string|null $name
 * @property string|null $link
 * @property array $coords
 * @method static Builder|Terminal newModelQuery()
 * @method static Builder|Terminal newQuery()
 * @method static Builder|Terminal query()
 * @method static Builder|Terminal whereCreatedAt($value)
 * @method static Builder|Terminal whereId($value)
 * @mixin Eloquent
 */
class Terminal extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'terminals';

    protected $fillable = [];
    protected $casts = [
        'coords' => 'array',
    ];
}
