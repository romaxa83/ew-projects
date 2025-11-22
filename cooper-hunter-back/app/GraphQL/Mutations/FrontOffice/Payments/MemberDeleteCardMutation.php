<?php

namespace App\GraphQL\Mutations\FrontOffice\Payments;

use App\Events\Payments\DeletePaymentCardFromMemberEvent;
use App\GraphQL\Types\NonNullType;
use App\Models\Payments\PaymentCard;
use App\Permissions\Payments\PaymentDeleteCardPermission;
use App\Repositories\Payment\PaymentCardRepository;
use App\Services\Payment\PaymentCardService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class MemberDeleteCardMutation extends BaseMutation
{
    public const NAME = 'memberDeletePaymentCard';
    public const PERMISSION = PaymentDeleteCardPermission::KEY;

    public function __construct(
        protected PaymentCardService $service,
        protected PaymentCardRepository $repo
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
        return NonNullType::boolean();
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
    ): bool
    {
        /** @var PaymentCard $card */
        $card = $this->repo->getBy('id', $args['id'], [], true);

        $copy = clone $card;

        $res = $this->service->remove($card);

        if($res){
            event(new DeletePaymentCardFromMemberEvent($copy));
        }

        return $res;
    }
}
