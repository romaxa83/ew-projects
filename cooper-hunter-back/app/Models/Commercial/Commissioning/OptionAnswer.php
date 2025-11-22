<?php

namespace App\Models\Commercial\Commissioning;

use App\Filters\Commercial\Commissioning\OptionAnswerFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Carbon\Carbon;
use Database\Factories\Commercial\Commissioning\ProtocolFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer id
 * @property string question_id
 * @property integer sort
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see OptionAnswer::question()
 * @property-read Question question
 *
 * @method static ProtocolFactory factory(...$parameters)
 */

class OptionAnswer extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use SetSortAfterCreate;
    use Filterable;

    public const TABLE = 'commissioning_option_answers';
    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'question_id',
    ];

    public function modelFilter(): string
    {
        return OptionAnswerFilter::class;
    }

    public function question(): HasOne
    {
        return $this->hasOne(Question::class, 'id','question_id');
    }
}
