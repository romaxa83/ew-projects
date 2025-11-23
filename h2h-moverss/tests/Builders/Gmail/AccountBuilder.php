<?php

namespace Tests\Builders\Gmail;

use App\Models\Division;
use App\Models\Mailbox\Gmail\Account;
use Tests\Builders\BaseBuilder;

class AccountBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Account::class;
    }

    public function division(Division $model): self
    {
        $this->data['division_id'] = $model->id;
        return $this;
    }
}
