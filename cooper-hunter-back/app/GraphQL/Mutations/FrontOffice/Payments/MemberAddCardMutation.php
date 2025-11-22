<?php

namespace App\GraphQL\Mutations\FrontOffice\Payments;

use App\Dto\Payments\PaymentCardDto;
use App\Events\Payments\AddPaymentCardToMemberEvent;
use App\GraphQL\InputTypes\Payments\PaymentCardAddInput;
use App\GraphQL\InputTypes\Utilities\Address\AddressInput;
use App\GraphQL\InputTypes\Utilities\Morph\MorphInput;
use App\GraphQL\Types\Payments\MemberPaymentCardType;
use App\Models\Payments\PaymentCard;
use App\Permissions\Payments\PaymentAddCardPermission;
use App\Services\Payment\PaymentCardService;
use App\Traits\Utilities\GetModelFromMorph;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberAddCardMutation extends BaseMutation
{
    use GetModelFromMorph;

    public const NAME = 'memberAddPaymentCard';
    public const PERMISSION = PaymentAddCardPermission::KEY;

    public function __construct(
        protected PaymentCardService $service
    )
    {
        $this->setDealerGuard();
    }

    public function args(): array
    {
        return [
            'morph' => MorphInput::nonNullType(),
            'payment_card' => PaymentCardAddInput::nonNullType(),
            'billing_address' => AddressInput::nonNullType()
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
        $dto = PaymentCardDto::byArgs($args);

        $card = makeTransaction(
            fn(): PaymentCard => $this->service->addCardToMember(
                $this->morphModel($dto->morph),
                $dto
            )
        );

        event(new AddPaymentCardToMemberEvent($card, $dto));

        return $card;
    }
}
