<?php

namespace App\Models\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\AnswerStatus;
use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Enums\Formats\DatetimeEnum;
use App\Models\BaseModel;
use App\Models\Commercial\CommercialProject;
use App\Traits\HasFactory;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\Commercial\Commissioning\ProjectProtocolFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property integer id
 * @property integer project_id
 * @property integer protocol_id
 * @property string status
 * @property int sort
 * @property Carbon|null closed_at
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see ProjectProtocol::protocol()
 * @property-read Protocol protocol
 *
 * @see CommercialProject::project()
 * @property-read CommercialProject project
 *
 * @see ProjectProtocolQuestion::projectQuestions()
 * @property-read ProjectProtocolQuestion projectQuestions
 *
 * @method static ProjectProtocolFactory factory(...$parameters)
 */

class ProjectProtocol extends BaseModel
{
    use HasFactory;

    public const TABLE = 'commercial_project_protocols';
    protected $table = self::TABLE;

    protected $casts = [
        'status' => ProtocolStatus::class,
        'closed_at' => DatetimeEnum::DEFAULT,
    ];

    protected $dates = [
        'closed_at',
    ];

    protected $fillable = [
        'sort',
    ];

    public function isDraft(): bool
    {
        return $this->status->value === ProtocolStatus::DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status->value === ProtocolStatus::PENDING;
    }

    public function isIssue(): bool
    {
        return $this->status->value === ProtocolStatus::ISSUE;
    }

    public function isDone(): bool
    {
        return $this->status->value === ProtocolStatus::DONE;
    }

    public function protocol(): HasOne
    {
        return $this->hasOne(Protocol::class, 'id', 'protocol_id');
    }

    public function projectQuestions(): HasMany|ProjectProtocolQuestion
    {
        return $this->hasMany(ProjectProtocolQuestion::class, 'project_protocol_id','id')->latest('sort');
    }

    public function project(): HasOne
    {
        return $this->hasOne(CommercialProject::class, 'id','project_id');
    }

    // compute attribute
    public function getTotalQuestionsAttribute(): int
    {
        return $this->projectQuestions->count();
    }

    public function getTotalCorrectAnswersAttribute(): int
    {
        return $this->projectQuestions->where('answer_status', AnswerStatus::ACCEPT)->count();
    }

    public function getTotalWrongAnswersAttribute(): int
    {
        return $this->projectQuestions->where('answer_status', AnswerStatus::REJECT)->count();
    }

    public function canClose(): bool
    {
        $can = true;
        foreach ($this->projectQuestions as $question){
            /** @var $question ProjectProtocolQuestion */
            if(!$question->answerIsAccept()){
                $can = false;
                break;
            }
        }

        return $can;
    }

    public function closeProtocol(): self
    {
        $this->status = ProtocolStatus::DONE;
        $this->closed_at = CarbonImmutable::now();
        $this->save();

        return $this;
    }
}


