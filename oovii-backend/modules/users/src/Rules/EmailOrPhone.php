<?php

namespace WezomCms\Users\Rules;

use Illuminate\Contracts\Validation\Rule;
use WezomCms\Users\Models\User;

class EmailOrPhone implements Rule
{
    protected $message;
    /**
     * @var bool
     */
    private $unique;

    /**
     * EmailOrPhone constructor.
     * @param  bool  $unique
     */
    public function __construct(bool $unique = false)
    {
        $this->unique = $unique;
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
        $field = User::emailOrPhone($value);

        if ($field === User::EMAIL) {
            $rules = ['email'];
            $messages = [
                'unique' => __('cms-users::site.auth.User with provided email already exists'),
            ];
        } else {
            $rules = ['regex:' . config('cms.core.phone.rules.phone.pattern')];
            $messages = [
                'unique' => __('cms-users::site.auth.User with provided phone already exists'),
            ];
        }

        if ($this->unique) {
            $rules[] = "unique:users,$field";
        }

        $validator = validator()
            ->make(
                [$attribute => $value],
                [$attribute => $rules],
                $messages,
                [$attribute => __('cms-users::site.auth.Email or phone')]
            );

        $passes = !$validator->fails();

        $this->message = $validator->getMessageBag()->first($attribute);

        return $passes;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return $this->message;
    }
}
