<?php

namespace Tests\Feature\Mutations\BackOffice\Content\OurCaseCategories;

use App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories\OurCaseCategoryToggleActiveMutation;
use App\Models\Content\OurCases\OurCaseCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\GraphQL\AssertToggleActiveTrait;

class OurCaseCategoryToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AssertToggleActiveTrait;

    public const MUTATION = OurCaseCategoryToggleActiveMutation::NAME;

    public function test_toggle_active(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertToggleActive(OurCaseCategory::factory()->create());
    }
}
