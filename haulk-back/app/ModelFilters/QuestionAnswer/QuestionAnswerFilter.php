<?php


namespace App\ModelFilters\QuestionAnswer;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class QuestionAnswerFilter extends ModelFilter
{

    /**
     * @param string $search
     * @return QuestionAnswerFilter
     */
    public function search(string $search)
    {
        return $this->where(function (Builder $query) use ($search) {
            return $query
                ->whereRaw('lower(question_en) like ?', ['%' . escapeLike(mb_convert_case($search, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(answer_en) like ?', ['%' . escapeLike(mb_convert_case($search, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(question_es) like ?', ['%' . escapeLike(mb_convert_case($search, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(answer_es) like ?', ['%' . escapeLike(mb_convert_case($search, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(question_ru) like ?', ['%' . escapeLike(mb_convert_case($search, MB_CASE_LOWER)) . '%'])
                ->orWhereRaw('lower(answer_ru) like ?', ['%' . escapeLike(mb_convert_case($search, MB_CASE_LOWER)) . '%']);
        });
    }
}
