<?php

namespace App\GraphQL\Mutations\BackOffice\Faq;

use App\Dto\Faq\FaqDto;
use App\GraphQL\InputTypes\Faq\FaqUpdateInput;
use App\GraphQL\Types\Faq\FaqType;
use App\Models\Faq\Faq;
use App\Permissions\Faq\FaqUpdatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Faq\FaqService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FaqUpdateMutation extends BaseMutation
{
    public const NAME = 'faqUpdate';
    public const PERMISSION = FaqUpdatePermission::KEY;

    public function __construct(protected FaqService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'faq' => [
                'type' => FaqUpdateInput::nonNullType(),
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
            fn() => $this->service->update(
                Faq::query()->findOrFail($args['faq']['id']),
                FaqDto::byArgs($args['faq'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'faq.id' => ['required', 'int', Rule::exists(Faq::TABLE, 'id')],
            'faq.translations' => [new TranslationsArrayValidator()],
        ];
    }
}
