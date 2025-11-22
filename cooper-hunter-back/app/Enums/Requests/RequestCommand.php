<?php

namespace App\Enums\Requests;

use Core\Enums\BaseEnum;

/**
 * @method static static CREATE_COMMERCIAL_PROJECT()
 * @method static static UPDATE_COMMERCIAL_PROJECT()
 */
class RequestCommand extends BaseEnum
{
    public const CREATE_COMMERCIAL_PROJECT = 'CreateCommercialProject';
    public const UPDATE_COMMERCIAL_PROJECT = 'UpdateCommercialProject';

    public const CREATE_DEALER = 'CreateDealer';
    public const UPDATE_DEALER = 'UpdateDealer';

    public const CREATE_COMPANY = 'CreateCompany';
    public const UPDATE_COMPANY = 'UpdateCompany';

    public const CREATE_DEALER_ORDER = 'CreateDealerOrder';
    public const UPDATE_DEALER_ORDER = 'UpdateDealerOrder';

    public const UPDATE_PACKING_SLIP = 'UpdatePackingSlip';

    public const ADD_PAYMENT_CARD_TO_MEMBER    = 'AddPaymentCardToMember';
    public const DELETE_PAYMENT_CARD_TO_MEMBER = 'DeletePaymentCardToMember';

    public function isCreateCommercialProject(): bool
    {
        return $this->is(self::CREATE_COMMERCIAL_PROJECT);
    }

    public function isUpdateCommercialProject(): bool
    {
        return $this->is(self::UPDATE_COMMERCIAL_PROJECT);
    }

    public function isCreateDealer(): bool
    {
        return $this->is(self::CREATE_DEALER);
    }

    public function isUpdateDealer(): bool
    {
        return $this->is(self::UPDATE_DEALER);
    }

    public function isCreateCompany(): bool
    {
        return $this->is(self::CREATE_COMPANY);
    }

    public function isUpdateCompany(): bool
    {
        return $this->is(self::UPDATE_COMPANY);
    }

    public function isAddPaymentCardToMember(): bool
    {
        return $this->is(self::ADD_PAYMENT_CARD_TO_MEMBER);
    }

    public function isDeletePaymentCardToMember(): bool
    {
        return $this->is(self::DELETE_PAYMENT_CARD_TO_MEMBER);
    }

    public function isUpdatePackingSlip(): bool
    {
        return $this->is(self::UPDATE_PACKING_SLIP);
    }
}
