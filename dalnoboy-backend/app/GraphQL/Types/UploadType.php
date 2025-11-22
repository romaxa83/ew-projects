<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use Core\Traits\GraphQL\Types\BaseTypeTrait;

class UploadType extends \Rebing\GraphQL\Support\UploadType
{
    use BaseTypeTrait;

    public const NAME = 'Upload';
}
