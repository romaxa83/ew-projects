<?php

namespace App\Filters\Faq;

use App\Filters\BaseModelFilter;
use App\Models\Faq\Question;
use App\Traits\Filter\IdFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Question
 */
class QuestionFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function query(string $query): void
    {
        $this->where(
            static function (Builder $b) use ($query) {
                $b
                    ->orWhereRaw(sprintf("LOWER(%s) LIKE ?", Question::TABLE . '.name'), ["%$query%"])
                    ->orWhereRaw(sprintf("LOWER(%s) LIKE ?", Question::TABLE . '.email'), ["%$query%"]);
            }
        );
    }

    public function admin(int $admin): void
    {
        $this->where('admin_id', $admin);
    }

    public function status(string $status): void
    {
        $this->where('status', $status);
    }
}
