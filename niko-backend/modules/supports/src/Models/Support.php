<?php

namespace WezomCms\Supports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 * @property int $id
 * @property bool $read
 * @property string $name
 * @property string $email
 * @property string $text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|self unread()
 * @mixin \Eloquent
 */
class Support extends Model
{
//    use Filterable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'supports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'text',
        'read'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'read' => 'bool',
    ];

    /**
     * @param  Builder  $query
     */
    public function scopeUnread(Builder $query)
    {
        $query->where('read', false);
    }
}



