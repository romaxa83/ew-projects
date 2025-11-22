<?php

namespace App\GraphQL\Mutations\BackOffice\Faq;

use App\GraphQL\Types\NonNullType;
use App\Models\Faq\Faq;
use App\Permissions\Faq\FaqDeletePermission;
use App\Services\Faq\FaqService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FaqDeleteMutation extends BaseMutation
{
    public const NAME = 'faqDelete';
    public const PERMISSION = FaqDeletePermission::KEY;

    public function __construct(protected FaqService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'faq_id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /** @throws Throwable */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(Faq::query()->findOrFail($args['faq_id']))
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'faq_id' => ['required', 'int', Rule::exists(Faq::TABLE, 'id')],
        ];
    }
}
