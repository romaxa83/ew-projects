<?php

namespace App\Models\Forms;

use Eloquent;
use Database\Factories\Forms\DraftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property array body
 * @property string path
 * @property int user_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 *
 * @mixin Eloquent
 *
 * @method static DraftFactory factory(...$parameters)
 */
class Draft extends Model
{
    use HasFactory;

    public const TABLE = 'drafts';
    protected $table = self::TABLE;

    protected $casts = [
        'body' => 'array',
    ];

    protected $fillable = [
        'body',
        'path',
    ];
}

