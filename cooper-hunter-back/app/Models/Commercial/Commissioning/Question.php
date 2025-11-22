<?php

namespace App\Models\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerPhotoType;
use App\Enums\Commercial\Commissioning\AnswerType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Filters\Commercial\Commissioning\QuestionFilter;
use App\Models\BaseModel;
use App\Models\Commercial\CommercialProject;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Carbon\Carbon;
use Database\Factories\Commercial\Commissioning\QuestionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer id
 * @property string answer_type
 * @property string photo_type      // статус фото в ответе, (нужно\ненужно\необязательно)
 * @property int protocol_id
 * @property QuestionStatus status
 * @property int sort
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read Collection|OptionAnswer[] optionAnswers
 *
 * @see Question::protocol()
 * @property-read Protocol protocol
 *
 * @see Question::projectProtocolQuestions()
 * @property-read Collection|ProjectProtocolQuestion[] projectProtocolQuestions
 *
 * @method static QuestionFactory factory(...$parameters)
 */
class Question extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use SetSortAfterCreate;
    use Filterable;

    public const TABLE = 'commissioning_questions';
    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'answer_type',
        'photo_type',
        'protocol_id',
        'active',
    ];

    protected $casts = [
        'status'=> QuestionStatus::class
    ];

    public function modelFilter(): string
    {
        return QuestionFilter::class;
    }

    public function isAnswerText(): bool
    {
        return $this->answer_type === AnswerType::TEXT;
    }

    public function isAnswerCheckbox(): bool
    {
        return $this->answer_type === AnswerType::CHECKBOX;
    }

    public function isAnswerRadio(): bool
    {
        return $this->answer_type === AnswerType::RADIO;
    }

    public function photoIsRequired(): bool
    {
        return $this->photo_type === AnswerPhotoType::REQUIRED;
    }

    public function photoIsNotRequired(): bool
    {
        return $this->photo_type === AnswerPhotoType::NOT_REQUIRED;
    }

    public function photoIsNotNecessary(): bool
    {
        return $this->photo_type === AnswerPhotoType::NOT_NECESSARY;
    }

    public function optionAnswers(): HasMany
    {
        return $this->hasMany(OptionAnswer::class, 'question_id', 'id');
    }

    public function projectProtocolQuestions(): HasMany
    {
        return $this->hasMany(ProjectProtocolQuestion::class);
    }

    public function protocol(): HasOne
    {
        return $this->hasOne(Protocol::class, 'id','protocol_id');
    }

    public function setRelationSort(): void
    {
        $this->projectProtocolQuestions()->update(['sort' => $this->sort]);
    }
}
