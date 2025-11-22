<?php

namespace App\Http\Requests\Payrolls;

class DeleteManyRequest extends MarkAsPaidRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('payrolls delete');
    }
}
