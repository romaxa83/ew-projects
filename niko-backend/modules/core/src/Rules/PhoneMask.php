<?php

namespace WezomCms\Core\Rules;

class PhoneMask extends Phone
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
            $pattern ?: config('cms.core.main.rules.phone_mask.pattern'),
            $formatMessage ?: config('cms.core.main.rules.phone_mask.format_message'),
            $message
        );
    }
}
