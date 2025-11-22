<?php

namespace App\Models\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolType;
use App\Enums\Commercial\Commissioning\QuestionStatus;
use App\Filters\Commercial\Commissioning\ProtocolFilter;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use App\Traits\Model\HasTranslations;
use App\Traits\Model\SetSortAfterCreate;
use Carbon\Carbon;
use Database\Factories\Commercial\Commissioning\OptionAnswerFactory;
use Database\Factories\Commercial\Commissioning\ProtocolFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer id
 * @property string type
 * @property integer sort
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read Collection|Question[] questions
 * @property-read Collection|Question[] questionsActive
 *
 * @see Protocol::projectProtocols()
 * @property-read Collection|ProjectProtocol[] projectProtocols
 *
 * @method static ProtocolFactory factory(...$parameters)
 */

class Protocol extends BaseModel
{
    use HasFactory;
    use SetSortAfterCreate;
    use Filterable;
    use HasTranslations;

    public const TABLE = 'commissioning_protocols';
    protected $table = self::TABLE;

    protected $fillable = [
        'sort',
        'type',
    ];

    public function isCommissioning(): bool
    {
        return $this->type === ProtocolType::COMMISSIONING;
    }

    public function isPreCommissioning(): bool
    {
        return $this->type === ProtocolType::PRE_COMMISSIONING;
    }

    public function modelFilter(): string
    {
        return ProtocolFilter::class;
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'protocol_id', 'id');
    }

    public function projectProtocols(): HasMany
    {
        return $this->hasMany(ProjectProtocol::class);
    }

    public function questionsActive(): HasMany
    {
        return $this->questions()->where('status', QuestionStatus::ACTIVE);
    }

    public function setRelationSort(): void
    {
        $this->projectProtocols()->update(['sort' => $this->sort]);
    }
}

