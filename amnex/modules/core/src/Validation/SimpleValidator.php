<?php

namespace Wezom\Core\Validation;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\MessageBag;

readonly class SimpleValidator implements Validator
{
    public function __construct(private array $messages)
    {
    }

    public function errors(): MessageBag
    {
        return new MessageBag($this->messages);
    }

    /** @phpstan-ignore-next-line  */
    public function getMessageBag()
    {
    }

    /** @phpstan-ignore-next-line  */
    public function validate()
    {
    }

    /** @phpstan-ignore-next-line  */
    public function validated()
    {
    }

    /** @phpstan-ignore-next-line  */
    public function fails()
    {
    }

    /** @phpstan-ignore-next-line  */
    public function failed()
    {
    }

    /** @phpstan-ignore-next-line  */
    public function sometimes($attribute, $rules, callable $callback)
    {
    }

    /** @phpstan-ignore-next-line  */
    public function after($callback)
    {
    }
}
