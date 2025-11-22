<?php

namespace App\Traits\Orders\Parts;

use App\Enums\Orders\Parts\OrderStatus;
use App\Foundations\Modules\Permission\Permissions\Order\Parts\OrderDeletePermission;

/**
 * @property-read OrderStatus $status
 */

trait CanActionScope
{
    public function canRefunded(): bool
    {
        return $this->status->statusIsFinal()
            && !$this->isDraft()
            && $this->isPaid()
            ;
    }

    public function canChangeStatus(): bool
    {
        return !($this->status->statusIsFinal() || $this->status->isNew()) && !$this->isDraft();
    }

    public function canCanceled(): bool
    {
        return $this->status->statusForEdit() && !$this->isDraft();
    }

    public function canAddPayment(): bool
    {
        return !$this->isDraft() && !$this->isPaid();
    }

    public function canUpdate(): bool
    {
        return $this->status->statusForEdit();
    }

    public function canDelete(): bool
    {
        if(auth_user()?->can(OrderDeletePermission::KEY)){

            return !$this->isPaid()
                && $this->status->statusForEdit()
                ;
        }

        return false;
    }

    public function canSendInvoice(): bool
    {
        return !$this->isDraft() && !$this->status->isCanceled();
    }

    public function canSendPaymentLink(): bool
    {
        return !$this->isDraft() && !$this->isPaid();
    }

    public function canAssignManger(): bool
    {
        return !$this->isDraft();
    }
}



