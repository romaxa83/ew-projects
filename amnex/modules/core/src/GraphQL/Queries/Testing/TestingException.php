<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Queries\Testing;

use Wezom\Core\GraphQL\BaseQuery;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Services\TestingExceptionService;

class TestingException extends BaseQuery
{
    public function __construct(protected TestingExceptionService $service)
    {
    }

    public function resolve(Context $context): bool
    {
        $this->service->createSomething($context);

        return true;
    }
}
