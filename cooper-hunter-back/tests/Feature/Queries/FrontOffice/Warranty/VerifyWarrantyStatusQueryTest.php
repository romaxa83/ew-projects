<?php

namespace Tests\Feature\Queries\FrontOffice\Warranty;

use App\Enums\Projects\Systems\WarrantyStatus;
use App\GraphQL\Queries\FrontOffice\Warranty\VerifyWarrantyStatusQuery;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Catalog\Products\ProductTranslation;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use App\Services\Warranty\WarrantyService;
use Carbon\Carbon;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VerifyWarrantyStatusQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = VerifyWarrantyStatusQuery::NAME;

    public function test_not_found(): void
    {
        $serial = 'not_exists_serial';

        $query = $this->getQuery($serial);

        $this->assertServerError($response = $this->postGraphQL($query), 'validation');

        $response->assertJson(
            [
                'errors' => [
                    [
                        'extensions' => [
                            'validation' => [
                                'serial_number' => [
                                    __('validation.custom.serial-number', ['serial' => strtoupper($serial)])
                                ],
                            ],
                        ],
                    ]
                ],
            ]
        );
    }

    protected function getQuery(string $serial): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'serial_number' => $serial,
                ]
            )
            ->select(
                [
                    'is_registered',
                    'information',
                    'purchase_date',
                    'installation_date',
                    'product' => [
                        'id',
                        'title',
                        'slug',
                        'translation' => [
                            'description',
                        ],
                    ],
                ]
            )
            ->make();
    }

    public function test_not_registered_yet(): void
    {
        $product = Product::factory()
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        $serial = $product->serialNumbers->first()->serial_number;

        $query = $this->getQuery($serial);

        $this->postGraphQL($query)
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'is_registered' => false,
                            'information' => __('messages.warranty.not_registered', ['serial' => strtoupper($serial)]),
                            'purchase_date' => null,
                            'installation_date' => null,
                        ],
                    ],
                ],
            );
    }

    public function test_registered_serial_number(): void
    {
        $products = Product::factory()
            ->times(5)
            ->has(
                ProductTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        /** @var Product $registered */
        $registered = $products->shift();

        WarrantyRegistrationUnitPivot::query()
            ->insert(
                [
                    'warranty_registration_id' => WarrantyRegistration::factory()->create()->id,
                    'product_id' => $registered->id,
                    'serial_number' => $serial = $registered->serialNumbers->first()->serial_number
                ]
            );

        $query = $this->getQuery($serial);

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonPath(
                'data.' . self::QUERY . '.information',
                __('messages.warranty.registered', ['status' => WarrantyStatus::PENDING()->description])
            )
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'is_registered',
                            'information',
                            'purchase_date',
                            'installation_date',
                            'product' => [
                                'id',
                                'title',
                                'slug',
                                'translation' => [
                                    'description',
                                ],
                            ],
                        ],
                    ],
                ]
            );
    }

//    public function test_not_registered_serial_number_status_delete(): void
//    {
//        $products = Product::factory()
//            ->times(5)
//            ->has(
//                ProductTranslation::factory()
//                    ->allLocales(),
//                'translations'
//            )
//            ->has(
//                ProductSerialNumber::factory(),
//                'serialNumbers'
//            )
//            ->create();
//
//        /** @var Product $registered */
//        $registered = $products->shift();
//
//        WarrantyRegistrationUnitPivot::query()
//            ->insert(
//                [
//                    'warranty_registration_id' => WarrantyRegistration::factory()->deleted()->create()->id,
//                    'product_id' => $registered->id,
//                    'serial_number' => $serial = $registered->serialNumbers->first()->serial_number
//                ]
//            );
//
//        $query = $this->getQuery($serial);
//
//        $this->postGraphQL($query)
//            ->assertJson(
//                [
//                    'data' => [
//                        self::QUERY => [
//                            'is_registered' => false,
//                            'information' => __('messages.warranty.not_registered', ['serial' => strtoupper($serial)]),
//                            'purchase_date' => null,
//                            'installation_date' => null,
//                        ],
//                    ],
//                ],
//            );
//    }

    public function test_old_warranty_registration_status(): void
    {
        Carbon::setTestNow(
            Carbon::parse(WarrantyService::CONSIDER_OLD_BEFORE)->subDay(),
        );

        $product = Product::factory()
            ->has(
                ProductTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->has(
                ProductSerialNumber::factory(),
                'serialNumbers'
            )
            ->create();

        WarrantyRegistrationUnitPivot::query()
            ->insert(
                [
                    'warranty_registration_id' => WarrantyRegistration::factory()->create()->id,
                    'product_id' => $product->id,
                    'serial_number' => $serial = $product->serialNumbers->first()->serial_number
                ]
            );

        $query = $this->getQuery($serial);

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonPath('data.' . self::QUERY . '.information', __('messages.warranty.registered_old'));
    }
}
