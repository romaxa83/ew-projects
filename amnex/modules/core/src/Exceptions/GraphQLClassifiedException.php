<?php

namespace Wezom\Core\Exceptions;

use GraphQL\Error\Error;
use Wezom\Core\Enums\GraphQLErrorClassification;

class GraphQLClassifiedException extends Error
{
    public function __construct(private readonly GraphQLErrorClassification $classification, Error $error)
    {
        parent::__construct(
            $error->getMessage(),
            $error->getNodes(),
            $error->getSource(),
            $error->getPositions(),
            $error->getPath(),
            $error->getPrevious(),
            $error->getExtensions()
        );
    }

    public function getExtensions(): ?array
    {
        return array_merge(
            ['classification' => $this->classification->name],
            parent::getExtensions() ?? []
        );
    }
}
