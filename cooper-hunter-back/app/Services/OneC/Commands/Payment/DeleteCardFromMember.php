<?php

namespace App\Services\OneC\Commands\Payment;

use App\Contracts\Utilities\Dispatchable;
use App\Enums\Requests\RequestCommand;
use App\Models\Payments\PaymentCard;
use App\Services\OneC\Commands\BaseCommand;

class DeleteCardFromMember extends BaseCommand
{
    public function nameCommand(): string
    {
        return RequestCommand::DELETE_PAYMENT_CARD_TO_MEMBER;
    }

    public function getUri(): string
    {
        return config("api.one_c.request_uri.payment.card.delete");
    }

    public function transformData(Dispatchable $model, array $additions = []): array
    {
        /** @var $model PaymentCard */
        return [
            'guid' => $model->guid
        ];
    }
}
