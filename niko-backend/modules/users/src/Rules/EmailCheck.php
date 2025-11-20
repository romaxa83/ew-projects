<?php

namespace WezomCms\Users\Rules;

use Illuminate\Contracts\Validation\Rule;
use WezomCms\Users\Models\User;

class EmailCheck implements Rule
{
    private $request;

    private $attr;
    private $val;

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
        $this->attr = $attribute;
        $this->val = $value;
        if(isset($this->request->all()['userId'])){
            if(User::query()->where('id', '!=', $this->request->all()['userId'])->where('email', $value)->exists()){
                return false;
            }
            return true;
        } else {
            if(User::query()->where('email', $value)->exists()){
                return false;
            }
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.user_exist', ['attribute' => $this->attr, 'value' => $this->val]);
    }
}
