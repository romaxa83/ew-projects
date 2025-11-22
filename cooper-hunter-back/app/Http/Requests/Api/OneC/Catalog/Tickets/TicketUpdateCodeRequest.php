<?php

namespace App\Http\Requests\Api\OneC\Catalog\Tickets;

use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Tickets\Ticket;
use App\Permissions\Catalog\Products\UpdatePermission;
use Illuminate\Validation\Rule;

class TicketUpdateCodeRequest extends BaseFormRequest
{
    public const PERMISSION = UpdatePermission::KEY;

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                Rule::unique(Ticket::class, 'code')
                    ->ignore($this->ticket)
            ]
        ];
    }
}