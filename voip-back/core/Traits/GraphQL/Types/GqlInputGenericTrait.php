<?php

declare(strict_types=1);

namespace Core\Traits\GraphQL\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

trait GqlInputGenericTrait
{
    use GqlBaseGenericTrait;

    public function toType(): Type|InputObjectType
    {
        return new InputObjectType(
            [
                'name' => $this->readGqlTypeName(),
                'description' => $this->readGqlTypeDescription(),
                'fields' => $this->readGqlTypeFields(),
            ]
        );
    }
}
