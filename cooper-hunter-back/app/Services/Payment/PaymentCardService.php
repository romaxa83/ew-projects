<?php

namespace App\Services\Payment;

use App\Contracts\Payment\PaymentModel;
use App\Dto\Payments\PaymentCardDto;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Payments\PaymentCard;
use Core\Exceptions\TranslatedException;

class PaymentCardService
{
    public function __construct()
    {}

    public function addCardToMember(PaymentModel $model, PaymentCardDto $dto): PaymentCard
    {
        /** @var $model Dealer|Company */

        $this->assertCard($model, $dto);

        $card = new PaymentCard();
        $card->member_type = $model::MORPH_NAME;
        $card->member_id = $model->id;
        $card->type = $dto->type;
        $card->code = $dto->lastFourNumericForCode();
        $card->expiration_date = $dto->expirationDate;
        $card->default = $model->cards->isEmpty();
        $card->hash = $dto->hash();
        $card->save();

        return $card;
    }

    public function assertCard(PaymentModel $model, PaymentCardDto $dto): void
    {
        if($model->cards()->where('hash', $dto->hash())->exists()){
            throw new TranslatedException(__('exceptions.payment.card.exist'), 502);
        }
    }

    public function toggleDefault(PaymentCard $model): PaymentCard
    {
        /** @var $model Dealer|Company */
        $newDefault = $model->member->cards->where('id', $model->id)->first();
        $oldDefault = $model->member->cards->where('default', true)->first();

//        if($newDefault == null){
//            throw new TranslatedException(__('exceptions.payment.card.not exist'), 502);
//        }

        $oldDefault->update(['default' => false]);
        $newDefault->update(['default' => true]);

        return $newDefault;
    }

    public function remove(PaymentCard $model): bool
    {
        return $model->delete();
    }
}

