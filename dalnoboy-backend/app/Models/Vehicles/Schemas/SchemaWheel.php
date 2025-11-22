<?php

namespace App\Models\Vehicles\Schemas;

use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\RuleInTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchemaWheel extends BaseModel
{
    use HasFactory;
    use RuleInTrait;

    public const TABLE = 'schema_wheels';

    protected $fillable = [
        'position',
        'name',
        'pos_x',
        'pos_y',
        'rotate',
        'use',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'pos_x' => 'int',
        'pos_y' => 'int',
        'rotate' => 'int',
        'use' => 'bool'
    ];

    public function axle(): BelongsTo
    {
        return $this->belongsTo(SchemaAxle::class, 'schema_axle_id', 'id');
    }

    public function required(): bool
    {
        $this->load(['axle.wheels']);

        if(count($this->axle->wheels) <=1){
            return false;
        }

        return true;

//        if(count($this->axle->wheels) == 2) {
//            return true;
//        }
//
//        $positions = array_column($this->axle->wheels->toArray(), 'position');
//
//        return !empty($positions) && (current($positions) == $this->position || last($positions) == $this->position);
    }
}
