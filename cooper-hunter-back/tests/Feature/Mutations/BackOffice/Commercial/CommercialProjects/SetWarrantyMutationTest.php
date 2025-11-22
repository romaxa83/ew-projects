<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\CommercialProjects;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\Enums\Warranties\WarrantyType;
use App\Events\Warranty\WarrantyRegistrationRequestedEvent;
use App\GraphQL\Mutations\BackOffice\Commercial\CommercialProjects\CommercialProjectSetWarrantyMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectAddition;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Commercial\ProjectAdditionBuilder;
use Tests\Builders\Commercial\ProjectBuilder;
use Tests\Builders\Commercial\ProjectUnitBuilder;
use Tests\TestCase;

class SetWarrantyMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialProjectSetWarrantyMutation::NAME;

    protected ProjectBuilder $projectBuilder;
    protected ProjectUnitBuilder $projectUnitBuilder;
    protected ProjectAdditionBuilder $projectAdditionBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->projectBuilder = resolve(ProjectBuilder::class);
        $this->projectUnitBuilder = resolve(ProjectUnitBuilder::class);
        $this->projectAdditionBuilder = resolve(ProjectAdditionBuilder::class);
    }

    /** @test */
    public function success_set(): void
    {
        Event::fake([WarrantyRegistrationRequestedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setEndCommissioningDate(now())->create();
        /** @var $addition CommercialProjectAddition */
        $addition = $this->projectAdditionBuilder->setProject($project)->create();

        $serialNumbers = ['0001', '0002', '0003'];

        $product_1 = Product::factory()->has(
            ProductSerialNumber::factory()->state(['serial_number' => $serial_1 = data_get($serialNumbers, '0')]),
            'serialNumbers'
        )->create();
        $product_2 = Product::factory()->has(
            ProductSerialNumber::factory()->state(['serial_number' => $serial_2 = data_get($serialNumbers, '1')]),
            'serialNumbers'
        )->create();
        $product_3 = Product::factory()->has(
            ProductSerialNumber::factory()->state(['serial_number' => $serial_3 = data_get($serialNumbers, '2')]),
            'serialNumbers'
        )->create();

        $u_1 = $this->projectUnitBuilder->setProject($project)->setProduct($product_1)
            ->setSerialNumber($serial_1)->create();
        $u_2 = $this->projectUnitBuilder->setProject($project)->setProduct($product_2)
            ->setSerialNumber($serial_2)->create();
        $u_3 = $this->projectUnitBuilder->setProject($project)->setProduct($product_3)
            ->setSerialNumber($serial_3)->create();

        $this->assertNull($project->warranty);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'success',
                        'message' => __('messages.commercial.set_warranty'),
                    ],
                ]
            ])
        ;

        $project->refresh();

        $this->assertEquals($project->warranty->warranty_status, WarrantyStatus::PENDING);
        $this->assertEquals($project->warranty->type, WarrantyType::COMMERCIAL);
        $this->assertEquals($project->warranty->member_type, $project->member_type);
        $this->assertEquals($project->warranty->member_id, $project->member_id);

        $this->assertFalse($project->warranty->user_info->is_user);
        $this->assertEquals($project->warranty->user_info->first_name, $project->first_name);
        $this->assertEquals($project->warranty->user_info->last_name, $project->last_name);
        $this->assertEquals($project->warranty->user_info->email, $project->email);
        $this->assertEquals($project->warranty->user_info->company_name, $project->company_name);
        $this->assertEquals($project->warranty->user_info->company_address, $project->company_address);

        $this->assertEquals($project->warranty->address->country_id, $project->country_id);
        $this->assertEquals($project->warranty->address->state_id, $project->state_id);
        $this->assertEquals($project->warranty->address->city, $project->city);
        $this->assertEquals($project->warranty->address->street, ' ');
        $this->assertEquals($project->warranty->address->zip, $project->zip);

        $this->assertEquals(
            $project->warranty->product_info->purchase_date,
            $project->additions->purchase_date->format('Y-m-d')
        );
        $this->assertEquals(
            $project->warranty->product_info->installation_date,
            $project->additions->purchase_date->format('Y-m-d')
        );
        $this->assertEquals(
            $project->warranty->product_info->installer_license_number,
            $project->additions->installer_license_number
        );
        $this->assertEquals(
            $project->warranty->product_info->purchase_place,
            $project->additions->purchase_place
        );

        $this->assertEquals($project->warranty->units[0]->id, $product_1->id);
        $this->assertEquals($project->warranty->units[0]->unit->serial_number, $serial_1);
        $this->assertEquals($project->warranty->units[1]->id, $product_2->id);
        $this->assertEquals($project->warranty->units[1]->unit->serial_number, $serial_2);
        $this->assertEquals($project->warranty->units[2]->id, $product_3->id);
        $this->assertEquals($project->warranty->units[2]->unit->serial_number, $serial_3);

        Event::assertDispatched(function (WarrantyRegistrationRequestedEvent $event) use ($serialNumbers) {
            return $event->getSerialNumbers() == $serialNumbers
                && $event->getNewStatus() == WarrantyStatus::PENDING
                ;
        });
    }

    /** @test */
    public function fail_not_close_commissioning(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'warning',
                        'message' => __('exceptions.commercial.warranty.not closed commissioning'),
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_not_have_units(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setEndCommissioningDate(now())->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'warning',
                        'message' => __('exceptions.commercial.warranty.not have units'),
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_has_warranty(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setEndCommissioningDate(now())->create();
        WarrantyRegistration::factory()->create([
            'commercial_project_id' => $project->id
        ]);
        $product_1 = Product::factory()->has(
            ProductSerialNumber::factory()->state(['serial_number' => $serial_1 = '0001']),
            'serialNumbers'
        )->create();
        $u_1 = $this->projectUnitBuilder->setProject($project)->setProduct($product_1)
            ->setSerialNumber($serial_1)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'warning',
                        'message' => __('exceptions.commercial.warranty.exist'),
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_has_not_addition(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $project CommercialProject */
        $project = $this->projectBuilder->setEndCommissioningDate(now())->create();

        $product_1 = Product::factory()->has(
            ProductSerialNumber::factory()->state(['serial_number' => $serial_1 = '0001']),
            'serialNumbers'
        )->create();
        $u_1 = $this->projectUnitBuilder->setProject($project)->setProduct($product_1)
            ->setSerialNumber($serial_1)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($project->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'type' => 'warning',
                        'message' => __('exceptions.commercial.warranty.not have additions'),
                    ],
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                ) {
                    type
                    message
                }
            }',
            self::MUTATION,
            $id,
        );
    }
}
