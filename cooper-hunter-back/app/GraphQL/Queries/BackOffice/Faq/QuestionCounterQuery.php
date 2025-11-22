<?php


namespace App\GraphQL\Queries\BackOffice\Faq;


use App\GraphQL\Types\Faq\QuestionCounterType;
use App\Models\Faq\Question;
use App\Permissions\Faq\Questions\QuestionListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class QuestionCounterQuery extends BaseQuery
{
    public const NAME = 'questionCounter';
    public const PERMISSION = QuestionListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return QuestionCounterType::nonNullType();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Collection
    {
        $result = Question::query()->selectRaw(
            "
            SUM(IF(admin_id IS NULL, 1, 0)) AS without_answer,
            SUM(IF(admin_id IS NULL, 0, 1)) AS with_answer,
            COUNT(*) AS total
            "
        )
            ->first();
        return collect([
            'without_answer' => $result->without_answer ?? 0,
            'with_answer' => $result->with_answer ?? 0,
            'total' => $result->total ?? 0,
        ]);
    }
}
