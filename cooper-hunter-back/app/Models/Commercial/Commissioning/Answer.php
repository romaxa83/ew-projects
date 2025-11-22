<?php

namespace App\Models\Commercial\Commissioning;

use App\Contracts\Media\HasMedia;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use App\Traits\Model\Media\InteractsWithMedia;
use Carbon\Carbon;
use Database\Factories\Commercial\Commissioning\AnswerFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property integer id
 * @property integer project_protocol_question_id
 * @property string|null text
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see OptionAnswer::optionAnswers()
 * @property-read OptionAnswer optionAnswers
 *
 * @method static AnswerFactory factory(...$parameters)
 */

class Answer extends BaseModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public const TABLE = 'commissioning_answers';
    protected $table = self::TABLE;

    public const MEDIA_COLLECTION_NAME = 'answers';
    public const MORPH_NAME = 'answers';

    public function getMediaCollectionName(): string
    {
        return self::MEDIA_COLLECTION_NAME;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_NAME)
            ->acceptsMimeTypes(array_merge(
                $this->mimeImage()
            ));
    }

    public function optionAnswers(): BelongsToMany
    {
        return $this->belongsToMany(
            OptionAnswer::class,
            AnswerOptionPivot::class,
            'answer_id',
            'option_answer_id'
        );
    }
}
