<?php

namespace App\Models\Commercial\Commissioning;

use App\Models\BasePivot;

/**
 * @property int answer_id
 * @property int option_answer_id
 */
class AnswerOptionPivot extends BasePivot
{
    public const TABLE = 'commissioning_answer_option_pivot';
    protected $table = self::TABLE;

    /** @var null */
    protected $primaryKey = null;
    public $timestamps = false;

    protected $fillable = [
        'answer_id',
        'option_answer_id',
    ];
}

