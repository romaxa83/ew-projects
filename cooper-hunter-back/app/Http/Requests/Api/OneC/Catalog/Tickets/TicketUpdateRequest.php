<?php

namespace App\Http\Requests\Api\OneC\Catalog\Tickets;

use App\Models\Catalog\Tickets\Ticket;
use App\Permissions\Catalog\Products\UpdatePermission;
use Illuminate\Validation\Rule;

class TicketUpdateRequest extends TicketCreateRequest
{
    public const PERMISSION = UpdatePermission::KEY;

    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['guid'], $rules['code']);

        return array_merge(
            $rules,
            [
                'code' => [
                    'required',
                    'string',
                    Rule::unique(Ticket::class, 'code')
                        ->ignore($this->ticket)
                ]
            ]
        );
    }

    protected function getDtoArgs(): array
    {
        return array_merge(
            [
                'guid' => $this->ticket->guid,
            ],
            parent::getDtoArgs()
        );
    }
}
