<?php

namespace WezomCms\Core\Rules;

use Lang;

class PhoneOrPhoneMask extends Phone
{
    /**
     * PhoneMask constructor.
     *
     * @param  string|null  $pattern
     * @param  string|null  $formatMessage
     * @param  string|null  $message
     */
    public function __construct(string $pattern = null, string $formatMessage = null, string $message = null)
    {
        parent::__construct(
            $pattern ?: config('cms.core.main.rules.phone_or_phone_mask.pattern'),
            $formatMessage ?: config('cms.core.main.rules.phone_or_phone_mask.format_message'),
            $message
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        $format = str_replace(':or', Lang::get('cms-core::' . app('side') . '.or'), $this->formatMessage);

        return Lang::get(
            $this->message ?: 'cms-core::' . app('side') . '.Phone is entered incorrectly',
            ['format' => $format]
        );
    }
}
