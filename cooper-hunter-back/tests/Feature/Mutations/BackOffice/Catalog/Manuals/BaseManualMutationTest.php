<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Manuals;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Storage\TestStorage;

abstract class BaseManualMutationTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;
}
