<?php

namespace App\Rules\Utils;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class VersionRule implements Rule
{
    private const COMPARISON_RULES = [
        'lower' => '<',
        'greater' => '>',
        'lowerOrEqual' => '<=',
        'greaterOrEqual' => '>='
    ];

    private string $comparisonRule;

    private string $message;
    private string $compareWith;
    private string $field;

    public function passes($attribute, $value): bool
    {
        $this->setMessage($attribute);

        if (!$this->isMatchPattern($value)) {
            return false;
        }

        if (empty($this->compareWith) || empty($this->comparisonRule)) {
            return true;
        }

        if (!$this->isMatchPattern($this->compareWith)) {
            $this->setMessage($this->field);

            return false;
        }

        $this->message = sprintf(
            '%s should be %s than %s',
            Str::headline($attribute),
            Str::snake(array_flip(self::COMPARISON_RULES)[$this->comparisonRule], ' '),
            Str::headline($this->field),
        );

        return version_compare($value, $this->compareWith, $this->comparisonRule);
    }

    private function setMessage(string $attribute): void
    {
        $this->message = __(
            'validation.custom.regex_format',
            [
                'attribute' => Str::headline($attribute),
                'format' => '#.#.#'
            ]
        );
    }

    public function isMatchPattern(mixed $value): bool
    {
        return (bool)preg_match('/^\d+\.\d+\.\d+$/', $value);
    }

    public function gt(string $version, string $field): self
    {
        $this->comparisonRule = self::COMPARISON_RULES['greater'];
        $this->compareWith = $version;
        $this->field = $field;

        return $this;
    }

    public function lt(string $version, string $field): self
    {
        $this->comparisonRule = self::COMPARISON_RULES['lower'];
        $this->compareWith = $version;
        $this->field = $field;

        return $this;
    }

    public function gte(string $version, string $field): self
    {
        $this->comparisonRule = self::COMPARISON_RULES['greaterOrEqual'];
        $this->compareWith = $version;
        $this->field = $field;

        return $this;
    }

    public function lte(string $version, string $field): self
    {
        $this->comparisonRule = self::COMPARISON_RULES['lowerOrEqual'];
        $this->compareWith = $version;
        $this->field = $field;

        return $this;
    }

    public function message(): string
    {
        return $this->message;
    }
}
