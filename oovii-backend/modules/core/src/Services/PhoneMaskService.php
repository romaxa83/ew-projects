<?php

namespace WezomCms\Core\Services;

class PhoneMaskService
{
    public const REPLACEABLE_SYMBOL = 'X';
    public const MASK_PLACEHOLDER = '_';

    /**
     * @var string
     */
    protected $format;

    /**
     * @var string|null
     */
    protected $presentDigitsRegex;

    /**
     * PhoneMask constructor.
     * @param  string|null  $format
     */
    public function __construct(?string $format = null)
    {
        $this->format = $format ?? config('cms.core.phone.mask_format');

        $this->presentDigitsRegex = $this->makePresentDigitsRegex($this->format);
    }

    /**
     * @param  string|null  $phone
     * @return string
     */
    public function applyMask(?string $phone): string
    {
        $phone = $this->removePhoneMask($phone);

        // Remove present "+" and digits
        if ($this->presentDigitsRegex !== null) {
            $phone = preg_replace($this->presentDigitsRegex, '', $phone);
        }

        $countReplaces = substr_count($this->format, static::REPLACEABLE_SYMBOL);

        $phone = mb_substr($phone, -$countReplaces);
        $phone = str_pad($phone, $countReplaces, static::MASK_PLACEHOLDER);

        $result = [];
        $replaceIndex = 0;
        foreach (str_split($this->format) as $char) {
            if ($char === static::REPLACEABLE_SYMBOL) {
                $char = $phone[$replaceIndex++];
            }
            $result[] = $char;
        }

        return implode($result);
    }

    /**
     * @param  string|null  $phone
     * @return string
     */
    public function removePhoneMask(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // Masked phone placeholder
        $maskedPhone = str_replace(static::REPLACEABLE_SYMBOL, static::MASK_PLACEHOLDER, $this->format);

        // First try remove masked empty string
        if (str_replace($maskedPhone, '', $phone) === '') {
            return null;
        }

        return preg_replace('/[^+\d]/', '', $phone);
    }

    /**
     * @param  string  $format
     * @return string|null
     */
    protected function makePresentDigitsRegex(string $format): ?string
    {
        $symbols = [];

        if (starts_with($format, '+')) {
            $symbols[] = '+';
        }

        // Extract all digits
        $matches = [];
        preg_match_all('/(\d)/', $format, $matches);
        foreach (array_get($matches, 1, []) as $symbol) {
            $symbols[] = $symbol;
        }

        return !empty($symbols) ? '/^' . preg_quote(implode($symbols)) . '/' : null;
    }
}
