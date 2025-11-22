<?php

namespace App\Models\Agreement;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $agreement_id
 * @property string $name
 * @property string $sum
 *
 */
class Job extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'agreement_jobs';
    protected $table = self::TABLE;
}
