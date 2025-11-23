<?php

namespace App\Models\Client;

use Database\Factories\Clients\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Client\Activity
 *
 * @property int $id
 * @property int $client_id
 * @property int $user_id
 * @property string $type
 * @property mixed|null $miscs
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Activity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity query()
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Activity whereUserId($value)
 * @method static ActivityFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Activity extends Model
{
    use HasFactory;

    public const MORPH_NAME = 'activity-client';

    protected $table = 'clients_activities';

    protected $casts = [
        'miscs' => 'json',
    ];

    protected static function newFactory(): ActivityFactory
    {
        return ActivityFactory::new();
    }
}
