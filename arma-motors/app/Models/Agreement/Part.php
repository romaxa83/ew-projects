<?php

namespace App\Models\Agreement;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $agreement_id
 * @property string $name
 * @property string $qty
 * @property string $sum
 *
 */
class Part extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'agreement_parts';
    protected $table = self::TABLE;
}
