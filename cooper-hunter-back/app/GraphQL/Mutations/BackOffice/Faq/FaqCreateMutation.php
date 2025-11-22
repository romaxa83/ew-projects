<?php

namespace App\GraphQL\Mutations\BackOffice\Faq;

use App\Dto\Faq\FaqDto;
use App\GraphQL\InputTypes\Faq\FaqCreateInput;
use App\GraphQL\Types\Faq\FaqType;
use App\Models\Faq\Faq;
use App\Permissions\Faq\FaqCreatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Faq\FaqService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FaqCreateMutation extends BaseMutation
{
    public const NAME = 'faqCreate';
    public const PERMISSION = FaqCreatePermission::KEY;

    public function __construct(protected FaqService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'faq' => [
                'type' => FaqCreateInput::nonNullType(),
            ],
        ];
    }

    public function type(): Type
    {
        return FaqType::type();
    }

    /** @throws Throwable */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): Faq
    {
        return makeTransaction(
            fn() => $this->service->create(FaqDto::byArgs($args['faq']))
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'faq.translations' => [new TranslationsArrayValidator()]
        ];
    }
}
