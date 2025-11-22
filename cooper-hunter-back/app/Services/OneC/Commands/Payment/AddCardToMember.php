<?php

namespace App\Services\OneC\Commands\Payment;

use App\Contracts\Utilities\Dispatchable;
use App\Dto\Payments\PaymentCardDto;
use App\Enums\Requests\RequestCommand;
use App\Models\Locations\State;
use App\Models\Payments\PaymentCard;
use App\Services\OneC\Commands\BaseCommand;

class AddCardToMember extends BaseCommand
{
    protected bool $saveData = false;

    public function nameCommand(): string
    {
        return RequestCommand::ADD_PAYMENT_CARD_TO_MEMBER;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.payment.card.add");
    }

    protected function afterRequest(Dispatchable $model, $response): void
    {
        /** @var $model PaymentCard */
        $model->update(['guid' => data_get($response, 'guid')]);
    }

    public function transformData(Dispatchable $model, array $additions = []): array
    {
        /** @var $model PaymentCard */
        /** @var $dto PaymentCardDto */
        $dto = data_get($additions, 'dto');
        if($dto === null){
            throw new \InvalidArgumentException("When generating data, there is no a dto");
        }

        /** @var $state State */
        $state = State::find($dto->billingAddress->stateID);

        return [
            'member' => [
                'type' => $model->member_type,
                'guid' => $model->member->guid
            ],
            'payment_card' => [
                'type' => $dto->type,
                'name' => $dto->name,
                'number' => $dto->number,
                'cvc' => $dto->cvc,
                'expiration_date' => $dto->expirationDate
            ],
            'billing_address' => [
                'country' => $state->country->country_code,
                'state' => $state->short_name,
                'city' => $dto->billingAddress->city,
                'address_line_1' => $dto->billingAddress->addressLine1,
                'address_line_2' => $dto->billingAddress->addressLine2,
                'zip' => $dto->billingAddress->zip,
            ],
        ];
    }
}
