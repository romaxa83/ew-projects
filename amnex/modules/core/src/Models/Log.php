<?php

declare(strict_types=1);

namespace Wezom\Core\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * \Wezom\Core\Models\Log
 *
 * @property int $id
 * @property string|null $type
 * @property string|null $method
 * @property array|null $json
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Log newModelQuery()
 * @method static Builder<static>|Log newQuery()
 * @method static Builder<static>|Log query()
 * @method static Builder<static>|Log whereCreatedAt($value)
 * @method static Builder<static>|Log whereId($value)
 * @method static Builder<static>|Log whereJson($value)
 * @method static Builder<static>|Log whereMethod($value)
 * @method static Builder<static>|Log whereType($value)
 * @method static Builder<static>|Log whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Log extends Model
{
    protected $table = 'logs';
    protected $fillable = ['json', 'type', 'method'];
    protected $casts = ['json' => 'array'];
}
