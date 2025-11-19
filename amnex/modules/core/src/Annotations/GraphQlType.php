<?php

namespace Wezom\Core\Annotations;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GraphQlType
{
    public function __construct(
        public string $type,
    ) {
    }
}
