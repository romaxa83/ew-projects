<?php

namespace App\GraphQL\Mutations\BackOffice\Orders\Dealer;

use App\Dto\Orders\Dealer\PaymentDescDto;
use App\Enums\Orders\Dealer\PaymentType;
use App\GraphQL\InputTypes\Orders\Dealer\PaymentDescInput;
use App\GraphQL\Types\Orders\Dealer\PaymentDesc\PaymentDescType;
use App\Models\About\Page;
use App\Services\About\PageService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdatePaymentDescMutation extends BaseMutation
{
    public const NAME = 'dealerOrderPaymentUpdate';

    public function __construct(
        protected PageService $service,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'input' => PaymentDescInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return PaymentDescType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Page
    {
        $dto = PaymentDescDto::byArgs($args['input']);

        $model = Page::query()->where('slug', $dto->type)->first();

        $model = makeTransaction(
            fn(): Page => $this->service->updateOnlyDesc($dto, $model)
        );

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.type' => ['required', 'string', PaymentType::ruleInDesc()],
        ];
    }
}
