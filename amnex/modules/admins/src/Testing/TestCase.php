<?php

declare(strict_types=1);

namespace Wezom\Admins\Testing;

use Illuminate\Foundation\Testing\WithFaker;
use Nuwave\Lighthouse\Testing\RefreshesSchemaCache;
use Wezom\Admins\Traits\AdminTestTrait;

abstract class TestCase extends \Wezom\Core\Testing\TestCase
{
    use AdminTestTrait;
    use RefreshesSchemaCache;
    use WithFaker;
}
