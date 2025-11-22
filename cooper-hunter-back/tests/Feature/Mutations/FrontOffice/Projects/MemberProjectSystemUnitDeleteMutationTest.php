<?php

namespace Tests\Feature\Mutations\FrontOffice\Projects;

use App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectSystemUnitDeleteMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\System;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberProjectSystemUnitDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = MemberProjectSystemUnitDeleteMutation::NAME;

    public function test_can_delete_units(): void
    {
        $this->loginAsUserWithRole();

        $system = System::factory()
            ->hasAttached(
                Product::factory(),
                ['serial_number' => 'serial_1234'],
                relationship: 'units'
            )
            ->create();

        $product = $system->units->first();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system_id' => $system->id,
                'unit_ids' => [$product->id],
            ],
        );

        $this->postGraphQL($query->getMutation())
            ->assertOk()
            ->assertJsonPath('data.' . self::MUTATION, true);
    }

    public function test_can_not_delete_units_with_warranty(): void
    {
        $this->loginAsUserWithRole();

        $system = System::factory()
            ->onWarranty()
            ->hasAttached(
                Product::factory(),
                ['serial_number' => 'serial_1234'],
                relationship: 'units'
            )
            ->create();

        $product = $system->units->first();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system_id' => $system->id,
                'unit_ids' => [$product->id],
            ],
        );

        $this->postGraphQL($query->getMutation())
            ->assertJsonPath('errors.0.message', __('Unable to remove units under warranty from the system'));
    }
}
