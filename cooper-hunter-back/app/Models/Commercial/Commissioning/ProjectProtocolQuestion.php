<?php

namespace App\Models\Commercial\Commissioning;

use App\Contracts\Alerts\AlertModel;
use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Models\BaseModel;
use App\Traits\HasFactory;
use Database\Factories\Commercial\Commissioning\ProjectProtocolQuestionFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer id
 * @property integer question_id
 * @property integer project_protocol_id
 * @property string answer_status
 * @property integer sort
 *
 * @see ProjectProtocol::projectProtocol()
 * @property-read ProjectProtocol projectProtocol
 *
 * @see Question::question()
 * @property-read Question question
 *
 * @see Answer::answer()
 * @property-read Answer|null answer
 *
 * @method static ProjectProtocolQuestionFactory factory(...$parameters)
 */

class ProjectProtocolQuestion extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'commercial_project_protocols_question';
    protected $table = self::TABLE;
    public const MORPH_NAME = 'protocol_question';

    protected $casts = [
        'answer_status' => AnswerStatus::class,
    ];

    protected $fillable = [
        'sort',
    ];

    public function question(): HasOne
    {
        return $this->hasOne(Question::class, 'id', 'question_id');
    }

    public function projectProtocol(): HasOne
    {
        return $this->hasOne(ProjectProtocol::class, 'id', 'project_protocol_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'id', 'project_protocol_question_id');
    }

    public function answerIsNone(): bool
    {
        return $this->answer_status->value === AnswerStatus::NONE;
    }

    public function answerIsDraft(): bool
    {
        return $this->answer_status->value === AnswerStatus::DRAFT;
    }

    public function answerIsAccept(): bool
    {
        return $this->answer_status->value === AnswerStatus::ACCEPT;
    }

    public function answerIsReject(): bool
    {
        return $this->answer_status->value === AnswerStatus::REJECT;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMorphType(): string
    {
        return self::MORPH_NAME;
    }
}



