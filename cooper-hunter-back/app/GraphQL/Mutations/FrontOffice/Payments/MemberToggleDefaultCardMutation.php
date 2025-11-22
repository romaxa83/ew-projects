<?php

namespace App\GraphQL\Mutations\FrontOffice\Payments;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Payments\MemberPaymentCardType;
use App\Models\Payments\PaymentCard;
use App\Permissions\Payments\PaymentAddCardPermission;
use App\Repositories\Payment\PaymentCardRepository;
use App\Services\Payment\PaymentCardService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberToggleDefaultCardMutation extends BaseMutation
{
    public const NAME = 'memberToggleDefaultPaymentCard';
    public const PERMISSION = PaymentAddCardPermission::KEY;

    public function __construct(
        protected PaymentCardService $service,
        protected PaymentCardRepository $repo,
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(PaymentCard::class, 'id')],
                'description' => 'MemberPaymentCardType ID'
            ],
        ];
    }

    public function type(): Type
    {
        return MemberPaymentCardType::nonNullType();
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
    ): PaymentCard
    {
        /** @var $card PaymentCard */
        $card = $this->repo->getBy('id', $args['id']);

        return makeTransaction(
            fn(): PaymentCard => $this->service->toggleDefault($card)
        );
    }
}

