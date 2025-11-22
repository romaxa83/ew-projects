<?php

namespace App\Models\QuestionAnswer;

use App\Scopes\CompanyScope;
use App\Traits\SetCompanyId;
use Illuminate\Database\Eloquent\Model;
use EloquentFilter\Filterable;
use App\ModelFilters\QuestionAnswer\QuestionAnswerFilter;

class QuestionAnswer extends Model
{
    use Filterable;
    use SetCompanyId;

    public const TABLE_NAME = 'questions_answers';

    private const DEFAULT_LANG = 'en';

    protected $table = self::TABLE_NAME;

    public $fillable = [
        'question_en',
        'answer_en',
        'question_es',
        'answer_es',
        'question_ru',
        'answer_ru',
    ];
    /**
     * @var mixed
     */
    private $question;
    /**
     * @var mixed
     */
    private $answer;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope());

        self::saving(function($model) {
            $model->setCompanyId();
        });
    }

    /**
     * @return string
     */
    public function modelFilter()
    {
        return $this->provideFilter(QuestionAnswerFilter::class);
    }

    public function getQuestion(string $lang)
    {
        if ($lang && $this->{'question_' . $lang}) {
            return $this->{'question_' . $lang};
        }

        return $this->{'question_' . self::DEFAULT_LANG};
    }

    public function getAnswer(string $lang)
    {
        if ($lang && $this->{'answer_' . $lang}) {
            return $this->{'answer_' . $lang};
        }

        return $this->{'answer_' . self::DEFAULT_LANG};
    }
}
