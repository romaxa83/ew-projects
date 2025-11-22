<?php

namespace App\GraphQL\Types;

use Core\Traits\GraphQL\Types\BaseTypeTrait;
use Rebing\GraphQL\Support\InputType;

abstract class BaseInputType extends InputType
{
    public const NAME = '';

    use BaseTypeTrait;
}
