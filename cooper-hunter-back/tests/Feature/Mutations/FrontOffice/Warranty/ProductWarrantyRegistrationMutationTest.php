<?php

namespace Tests\Feature\Mutations\FrontOffice\Warranty;

use App\GraphQL\Mutations\FrontOffice\Warranty\ProductWarrantyRegistrationMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Products\UnitType;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Models\Users\User;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\WithFaker;

class ProductWarrantyRegistrationMutationTest extends SystemWarrantyRegistrationMutationTest
{
    use WithFaker;

    public const MUTATION = ProductWarrantyRegistrationMutation::NAME;

    public function test_can_not_register_used_serial_number(): void
    {
        $products = Product::factory()
            ->times(5)
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        /** @var Product $registered */
        $registered = $products->shift();

        WarrantyRegistrationUnitPivot::query()->insert(
            [
                'warranty_registration_id' => WarrantyRegistration::factory()->create()->id,
                'product_id' => $registered->id,
                'serial_number' => $registeredSerial = $registered->serialNumbers->first()->serial_number
            ]
        );

        /** @var Product $notRegistered */
        $notRegistered = $products->shift();

        $query = $this->getQuery([$registeredSerial, $notRegistered->serialNumbers->first()->serial_number]);

        $this->assertServerError($this->postGraphQL($query), 'validation');
    }

//    public function test_can_register_used_serial_number_if_status_deleted(): void
//    {
//        $products = Product::factory()
//            ->times(5)
//            ->has(
//                ProductSerialNumber::factory(),
//                'serialNumbers'
//            )
//            ->create();
//
//        /** @var Product $registered */
//        $registered = $products->shift();
//
//        WarrantyRegistrationUnitPivot::query()->insert(
//            [
//                'warranty_registration_id' => WarrantyRegistration::factory()->deleted()->create()->id,
//                'product_id' => $registered->id,
//                'serial_number' => $registeredSerial = $registered->serialNumbers->first()->serial_number
//            ]
//        );
//
//        /** @var Product $notRegistered */
//        $notRegistered = $products->shift();
//
//        $query = $this->getQuery([$registeredSerial, $notRegistered->serialNumbers->first()->serial_number]);
//
//        $this->postGraphQL($query)
//            ->dump();
//
////        $this->assertServerError(, 'validation');
//    }

    protected function getQuery(array $serialNumbers): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'serial_numbers' => $serialNumbers,
                    'user' => $this->getUserInfo(),
                    'address' => $this->getAddressInfo(),
                    'product' => $this->getProductInfo(),
                ]
            )
            ->select(
                [
                    'message',
                    'type',
                ]
            )
            ->make();
    }

    public function test_register_units_by_guest(): void
    {
        $monoblock = UnitType::query()->where('name', 'monoblock')->first();
        $product = Product::factory()
            ->has(ProductSerialNumber::factory(), 'serialNumbers')
            ->create(['unit_type_id' => $monoblock->id]);

        $query = $this->getQuery([$product->serialNumbers->first()->serial_number]);

        $this->assertOk(
            $this->postGraphQL($query),
            self::MUTATION
        )
        ;

        $this->assertWarrantyRegistrationAccepted();
    }

//    /** @test */
//    public function fail_register_units_by_guest_first_indoor(): void
//    {
//        $indoor = UnitType::query()->where('name', 'indoor')->first();
//        $product = Product::factory()
//            ->has(ProductSerialNumber::factory(), 'serialNumbers')
//            ->create(['unit_type_id' => $indoor->id]);
//
//        $query = $this->getQuery([$product->serialNumbers->first()->serial_number]);
//
//        $this->postGraphQL($query)->dump();
//
//
//        $this->assertWarrantyRegistrationAccepted();
//    }

    public function test_register_units_by_user(): void
    {
        $user = $this->loginAsUser();

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        $query = $this->getQuery([$product->serialNumbers->first()->serial_number]);

        $this->assertOk(
            $this->postGraphQL($query),
            self::MUTATION
        );

        $this->assertWarrantyRegistrationAccepted($user);
    }

    public function test_it_change_status_for_exists_system_even_if_warranty_was_requested_by_other_person(): void
    {
        $serial = 'serial_1234';

        $systemOwner = User::factory()->create();

        Product::factory()
            ->has(
                ProductSerialNumber::factory()
                    ->state(['serial_number' => $serial]),
                'serialNumbers'
            )
            ->create();

        $system = System::factory()
            ->for(
                Project::factory()
                    ->for($systemOwner, 'member')
            )
            ->hasAttached(
                Product::factory(),
                ['serial_number' => $serial],
                'units'
            )
            ->create();

        $query = $this->getQuery([$serial]);

        self::assertTrue($system->warranty_status->notRegistered());

        $this->assertOk(
            $this->postGraphQL($query),
            self::MUTATION
        );

        $this->assertWarrantyRegistrationAccepted();

        self::assertTrue($system->fresh()->warranty_status->isPending());
    }

    public function test_register_by_technician(): void
    {
        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        $query = $this->getQueryForTechnician([$product->serialNumbers->first()->serial_number]);

        $this->assertOk(
            $this->postGraphQL($query),
            self::MUTATION
        );

        $this->assertWarrantyRegistrationAccepted();
    }

    protected function getQueryForTechnician(array $serialNumbers): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'serial_numbers' => $serialNumbers,
                    'technician' => $this->getTechnicianInfo(),
                    'address' => $this->getAddressInfo(),
                    'product' => $this->getProductInfo(),
                ]
            )
            ->select(
                [
                    'message',
                    'type',
                ]
            )
            ->make();
    }

    protected function getTechnicianInfo(): array
    {
        return [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'email@example.com',
            'company_name' => 'Company name',
            'company_address' => 'Company address avenue',
        ];
    }

    public function test_register_one_product_with_many_serials(): void
    {
        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()->times(5),
                'serialNumbers'
            )
            ->create();

        $this->assertDatabaseCount(WarrantyRegistrationUnitPivot::TABLE, 0);

        $query = $this->getQueryForTechnician($product->serialNumbers->pluck('serial_number')->toArray());

        $this->assertOk(
            $this->postGraphQL($query),
            self::MUTATION
        );

        $this->assertDatabaseCount(WarrantyRegistrationUnitPivot::TABLE, 5);
    }

    /** @test */
    public function fail_duplicate_serial_numbers(): void
    {
        $this->loginAsUser();

        $product_1 = Product::factory()->has(
                ProductSerialNumber::factory(), 'serialNumbers'
            )->create();
        $product_2 = Product::factory()->has(
            ProductSerialNumber::factory(), 'serialNumbers'
        )->create();

        $serialNumber = $product_1->serialNumbers->first()->serial_number;
        $query = $this->getQuery([
            $serialNumber,
            $product_2->serialNumbers->first()->serial_number,
            $serialNumber
        ]);

        $res = $this->postGraphQL($query);

        $this->assertResponseHasValidationMessage($res, 'serial_numbers.0', [__('validation.custom.duplicate_serial_numbers')]);
        $this->assertResponseHasValidationMessage($res, 'serial_numbers.1', [__('validation.custom.duplicate_serial_numbers')]);
        $this->assertResponseHasValidationMessage($res, 'serial_numbers.2', [__('validation.custom.duplicate_serial_numbers')]);
    }
}
