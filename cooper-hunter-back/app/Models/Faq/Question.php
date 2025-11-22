<?php

namespace App\Models\Faq;

use App\Casts\EmailCast;
use App\Enums\Faq\Questions\QuestionStatusEnum;
use App\Filters\Faq\QuestionFilter;
use App\Models\Admins\Admin;
use App\Models\BaseModel;
use App\Traits\Filterable;
use App\Traits\HasFactory;
use BenSampo\Enum\Traits\CastsEnums;
use Database\Factories\Faq\QuestionFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int|null admin_id
 * @property string status
 * @property string name
 * @property string email
 * @property string question
 * @property string|null answer
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @method static QuestionFactory factory(...$parameters)
 */
class Question extends BaseModel
{
    use Filterable;
    use HasFactory;
    use CastsEnums;

    public const TABLE = 'questions';

    protected $table = self::TABLE;

    protected $casts = [
        'status' => QuestionStatusEnum::class,
        'email' => EmailCast::class,
    ];

    public function modelFilter(): string
    {
        return QuestionFilter::class;
    }

    public function admin(): BelongsTo|Admin
    {
        return $this->belongsTo(Admin::class);
    }

    public function getEmailString(): string
    {
        return (string)$this->email;
    }

    public function isAnswered(): bool
    {
        return (bool)$this->admin_id;
    }
}
