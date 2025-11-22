<?php

namespace Tests\Feature\Mutations\BackOffice\Content\OurCases;

use App\GraphQL\Mutations\BackOffice\Content\OurCases\OurCaseToggleActiveMutation;
use App\Models\Content\OurCases\OurCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\GraphQL\AssertToggleActiveTrait;

class OurCaseToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AssertToggleActiveTrait;

    public const MUTATION = OurCaseToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertToggleActive(OurCase::factory()->create());
    }
}
