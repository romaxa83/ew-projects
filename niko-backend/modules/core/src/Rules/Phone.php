<?php

namespace WezomCms\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Lang;

class Phone implements Rule
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var string
     */
    protected $formatMessage;

    /**
     * @var string|null
     */
    protected $message;

    /**
     * Phone constructor.
     *
     * @param  string|null  $pattern
     * @param  string|null  $formatMessage
     * @param  string|null  $message
     */
    public function __construct(string $pattern = null, string $formatMessage = null, string $message = null)
    {
        $this->pattern = $pattern ?: config('cms.core.main.rules.phone.pattern');

        $this->formatMessage = $formatMessage ?: config('cms.core.main.rules.phone.format_message');

        $this->message = $message;
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
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return preg_match($this->pattern, $value) > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return Lang::get(
            $this->message ?: 'cms-core::' . app('side') . '.Phone is entered incorrectly',
            ['format' => $this->formatMessage]
        );
    }

    /**
     * Convert the rule to a validation string.
     *
     * @return string
     */
    public function __toString()
    {
        return "regex:{$this->pattern}";
    }
}
