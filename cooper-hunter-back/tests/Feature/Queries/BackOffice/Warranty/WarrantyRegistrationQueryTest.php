<?php

namespace Tests\Feature\Queries\BackOffice\Warranty;

use App\GraphQL\Queries\BackOffice\Warranty\WarrantyRegistrations\WarrantyRegistrationQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Technicians\Technician;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Database\Factories\Warranty\WarrantyRegistrationFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarrantyRegistrationQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const QUERY = WarrantyRegistrationQuery::NAME;

    public function test_list(): void
    {
        $this->loginAsSuperAdmin();

        $this->createWarrantyRegistration(
            WarrantyRegistration::factory()
                ->forMember(Technician::factory())
        );

        $this->createWarrantyRegistration(WarrantyRegistration::factory());

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                $this->getSelect()
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                $this->getJsonStructure()
                            ],
                        ],
                    ]
                ]
            );
    }

    protected function createWarrantyRegistration(WarrantyRegistrationFactory|WarrantyRegistration $warranty): void
    {
        if ($warranty instanceof WarrantyRegistrationFactory) {
            $warranty = $warranty->create();
        }

        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory()->state(['serial_number' => $serial = $this->faker->bothify]),
                'serialNumbers'
            )
            ->create();

        WarrantyRegistrationUnitPivot::query()
            ->insert(
                [
                    'warranty_registration_id' => $warranty->id,
                    'product_id' => $product->id,
                    'serial_number' => $serial,
                ]
            );
    }

    protected function getSelect(): array
    {
        return [
            'data' => [
                'id',
                'warranty_status',
                'type',
                'member' => [
                    'id',
                    'name',
                ],
                'user_info' => [
                    'member_type',
                    'first_name',
                    'last_name',
                    'email',
                    'company_name',
                    'company_address',
                ],
                'system' => [
                    'id',
                    'name'
                ],
                'units' => [
                    'id',
                    'serial_number',
                    'title',
                ],
            ],
        ];
    }

    protected function getJsonStructure(): array
    {
        return [
            'id',
            'warranty_status',
            'type',
            'member' => [
                'id',
                'name',
            ],
            'user_info' => [
                'member_type',
                'first_name',
                'last_name',
                'email',
                'company_name',
                'company_address',
            ],
            'system' => [
                'id',
                'name'
            ],
            'units' => [
                [
                    'id',
                    'serial_number',
                    'title',
                ]
            ],
        ];
    }

    public function test_filter_by_member_name(): void
    {
        $this->loginAsSuperAdmin();

        $this->createWarrantyRegistration(
            WarrantyRegistration::factory()
                ->forMember($t = Technician::factory()->create())
                ->create()
        );

        $this->createWarrantyRegistration(WarrantyRegistration::factory());

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'member_name' => $t->first_name
                ]
            )
            ->select(
                $this->getSelect()
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                $this->getJsonStructure()
                            ],
                        ],
                    ]
                ]
            );
    }

    public function test_filter_by_user_info(): void
    {
        $this->loginAsSuperAdmin();

        $this->createWarrantyRegistration($w = WarrantyRegistration::factory()->create());
        $this->createWarrantyRegistration(WarrantyRegistration::factory());
        $this->createWarrantyRegistration(WarrantyRegistration::factory());

        $firstName = $w->user_info->first_name;

        $query = GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'member_name' => $firstName
                ]
            )
            ->select(
                $this->getSelect()
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(1, 'data.' . self::QUERY . '.data')
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                $this->getJsonStructure()
                            ],
                        ],
                    ]
                ]
            );
    }
}
