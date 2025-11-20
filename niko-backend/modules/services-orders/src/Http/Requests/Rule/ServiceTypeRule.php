<?php

namespace WezomCms\ServicesOrders\Http\Requests\Rule;

use Illuminate\Contracts\Validation\Rule;
use WezomCms\Services\Types\ServiceType;

// валидаци для создания или выбор клиента
class ServiceTypeRule implements Rule
{
    private $request;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        dd('d');
//        dd(($this->request->all()['type'] !== ServiceType::TYPE_STO) && ($this->request->all()['type'] !== ServiceType::TYPE_INSURANCE));
//        dd(ServiceType::TYPE_STO, $this->request);
        if(($this->request->all()['type'] !== ServiceType::TYPE_STO) && ($this->request->all()['type'] !== ServiceType::TYPE_INSURANCE)){

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        // TODO: Implement message() method.
    }
}
