<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Unit\Source;

use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;

class TestingFieldResolver extends BaseFieldResolver
{
    public function __construct(protected TestingService $service)
    {
    }

    public function resolve(Context $context): void
    {
        $this->service->createSomething($context);
    }
}
