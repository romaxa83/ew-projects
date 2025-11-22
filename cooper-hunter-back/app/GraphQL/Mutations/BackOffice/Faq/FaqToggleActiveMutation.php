<?php

namespace App\GraphQL\Mutations\BackOffice\Faq;

use App\GraphQL\Types\Faq\FaqType;
use App\GraphQL\Types\NonNullType;
use App\Models\Faq\Faq;
use App\Permissions\Faq\FaqUpdatePermission;
use App\Services\Faq\FaqService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class FaqToggleActiveMutation extends BaseMutation
{
    public const NAME = 'faqToggleActive';
    public const PERMISSION = FaqUpdatePermission::KEY;

    public function __construct(protected FaqService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return FaqType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Faq
    {
        $faq = Faq::query()->findOrFail($args['id']);

        return $this->service->toggle($faq);
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Faq::TABLE, 'id')],
        ];
    }

}
