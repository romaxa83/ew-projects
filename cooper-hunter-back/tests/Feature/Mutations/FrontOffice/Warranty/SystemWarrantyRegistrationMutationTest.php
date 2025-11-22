<?php

namespace Tests\Feature\Mutations\FrontOffice\Warranty;

use App\Entities\Warranty\WarrantyUserInfo;
use App\GraphQL\Mutations\FrontOffice\Warranty\SystemWarrantyRegistrationMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Models\Users\User;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Core\Enums\Messages\MessageTypeEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SystemWarrantyRegistrationMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SystemWarrantyRegistrationMutation::NAME;

    public function test_can_not_request_already_under_warranty_system(): void
    {
        $user = $this->loginAsUserWithRole();

        $system = System::factory()
            ->for(
                Project::factory()
                    ->for($user, 'member')
            )
            ->hasAttached(
                Product::factory(),
                [
                    'serial_number' => 'serial_123',
                ],
                'units'
            )
            ->onWarranty()
            ->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system_id' => $system->id,
                'user' => $this->getUserInfo(),
                'address' => $this->getAddressInfo(),
                'product' => $this->getProductInfo(),
            ],
            [
                'message',
                'type',
            ]
        );

        $this->assertRegistrationAlreadySent(
            $this->postGraphQL(
                $query->getMutation()
            ),
            self::MUTATION
        );
    }

    protected function getUserInfo(): array
    {
        return [
            'first_name' => 'First',
            'last_name' => 'Last',
            'email' => 'email@example.com',
        ];
    }

    protected function getAddressInfo(): array
    {
        $state = State::first();
        $country = Country::first();

        return [
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'street' => 'Street 1',
            'city' => 'New York',
            'zip' => '0000',
        ];
    }

    protected function getProductInfo(): array
    {
        return [
            'purchase_date' => '2000-01-01',
            'installation_date' => '2000-01-01',
            'installer_license_number' => '2000',
            'purchase_place' => '2000',
        ];
    }

    protected function assertRegistrationAlreadySent(TestResponse $response, string $mutation): void
    {
        $response->assertJson(
            [
                'data' => [
                    $mutation => [
                        'message' => __('Warranty registration request already sent.'),
                        'type' => MessageTypeEnum::WARNING
                    ],
                ],
            ]
        );
    }

    public function test_can_not_register_foreign_system(): void
    {
        $this->loginAsUserWithRole();

        $system = System::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system_id' => $system->id,
                'user' => $this->getUserInfo(),
                'address' => $this->getAddressInfo(),
                'product' => $this->getProductInfo(),
            ],
            [
                'message',
                'type',
            ]
        );

        $this->assertServerError(
            $this->postGraphQL($query->getMutation()),
            'validation'
        );
    }

    public function test_register_system(): void
    {
        $user = $this->loginAsUserWithRole();

        $system = System::factory()
            ->for(
                Project::factory()
                    ->for($user, 'member')
            )
            ->hasAttached(
                Product::factory(),
                [
                    'serial_number' => 'serial_123',
                ],
                'units'
            )
            ->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'system_id' => $system->id,
                'user' => $this->getUserInfo(),
                'address' => $this->getAddressInfo(),
                'product' => $this->getProductInfo(),
            ],
            [
                'message',
                'type',
            ]
        );

        $this->assertDatabaseCount(WarrantyRegistration::TABLE, 0);

        $this->assertOk(
            $this->postGraphQL(
                $query->getMutation()
            ),
            self::MUTATION
        );

        $this->assertWarrantyRegistrationAccepted($user, $system);
    }

    protected function assertOk(TestResponse $response, string $mutation): void
    {
        $response->assertJson(
            [
                'data' => [
                    $mutation => [
                        'message' => __('Warranty registration request sent'),
                        'type' => MessageTypeEnum::SUCCESS
                    ],
                ],
            ]
        );
    }

    protected function assertWarrantyRegistrationAccepted(?User $user = null, ?System $system = null): void
    {
        $this->assertDatabaseCount(WarrantyRegistration::TABLE, 1);

        if ($user) {
            $this->assertDatabaseHas(
                WarrantyRegistration::TABLE,
                [
                    'member_type' => $user->getMorphType(),
                    'member_id' => $user->getId(),
                    'system_id' => $system->id ?? null,
                ],
            );
        }

        $this->assertDatabaseCount(WarrantyRegistrationUnitPivot::TABLE, 1);
    }

    public function test_warranty_factory(): void
    {
        $user = WarrantyUserInfo::make(
            [
                'first_name' => $first = 'First',
                'last_name' => $last = 'Last',
                'email' => $email = 'example@email.com',
            ],
            true
        );

        $warranty = WarrantyRegistration::factory()
            ->create(
                [
                    'user_info' => $user
                ]
            );

        self::assertEquals($first, $warranty->user_info->first_name);
        self::assertEquals($last, $warranty->user_info->last_name);
        self::assertEquals($email, $warranty->user_info->email);
    }
}
