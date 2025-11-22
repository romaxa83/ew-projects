<?php

namespace App\Http\Requests\Inventories\Transaction;

use App\Http\Requests\Common\PaginationRequest;

class TransactionIndexRequest extends PaginationRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
