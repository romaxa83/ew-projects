<?php

namespace Tests\Feature\Mutations\FrontOffice\Warranty;

use App\GraphQL\Mutations\FrontOffice\Warranty\SystemCanRegisterWarrantyMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SystemCanRegisterWarrantyMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SystemCanRegisterWarrantyMutation::NAME;

    public function test_can_not_register_system(): void
    {
        $user = $this->loginAsUserWithRole();

        $system = System::factory()
            ->for(
                Project::factory()
                    ->for($user, 'member')
            )
            ->create();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        WarrantyRegistrationUnitPivot::query()->insert(
            [
                'warranty_registration_id' => WarrantyRegistration::factory()->create()->id,
                'product_id' => $product->id,
                'serial_number' => $serial = $product->serialNumbers->first()->serial_number
            ]
        );

        $system->units()->syncWithPivotValues($product, ['serial_number' => $serial]);

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'system_id' => $system->id,
                ]
            )
            ->make();

        $this->assertServerError(
            $this->postGraphQL($query),
            __('Warranty registration for these serial numbers has already been requested')
        );
    }

    public function test_can_register_system(): void
    {
        $user = $this->loginAsUserWithRole();

        $system = System::factory()
            ->for(
                Project::factory()
                    ->for($user, 'member')
            )
            ->create();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        $system->units()->syncWithPivotValues(
            $product,
            ['serial_number' => $product->serialNumbers->first()->serial_number]
        );

        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'system_id' => $system->id,
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => true,
                    ]
                ]
            );
    }
}
