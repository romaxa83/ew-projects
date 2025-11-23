<?php


namespace Tests\Feature\Modules\Users\V1\Address;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Orders\Models\UserAddress;
use WezomCms\Users\Models\User;

class UserAddressTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function test_unauthenticated(): void
    {
        $this->postJson(route('api.v1.mobile.addresses.store'), [])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse(__("cms-core::site.Unauthenticated")));
    }

    public function test_user_can_add_delivery_address(): void
    {
        $user = $this->loginAsUser();
        $addressData = $this->getAddressData();

        $res = $this->postJson(route('api.v1.mobile.addresses.store'), $addressData)
            ->assertCreated()
            ->assertJsonStructure($this->structureResource([
                'id',
                'region_code',
                'region_name',
                'city_code',
                'city_name',
                'postal_code',
                'address',
                'name',
            ]));

        $address = $res->json('data');

        self::assertEquals($addressData['region_code'], $address['region_code']);
        self::assertEquals($addressData['city_code'], $address['city_code']);
        self::assertEquals($addressData['postal_code'], $address['postal_code']);
        self::assertEquals($addressData['address'], $address['address']);
        self::assertEquals($addressData['name'], $address['name']);

        $this->assertDatabaseHas(
            UserAddress::TABLE,
            [
                'id' => $address['id'],
                'user_id' => $user->id,
                'region_code' => $address['region_code'],
                'city_code' => $address['city_code'],
                'postal_code' => $address['postal_code'],
                'address' => $address['address'],
                'name' => $address['name'],
            ]
        );
    }

    public function test_user_can_delete_delivery_address(): void
    {
        $user = $this->loginAsUser();

        /** @var UserAddress $address1 */
        $address1 = UserAddress::factory()->create(['user_id' => $user->id]);
        UserAddress::factory()->create(['user_id' => $user->id]);

        $this->deleteJson(route('api.v1.mobile.addresses.destroy', [ 'address' => $address1->id ]))
            ->assertOk()
            ->assertJson($this->structureSucessResponse(__('cms-orders::site.User address deleted')));

        self::assertCount(1, $user->addresses);

        $this->assertDatabaseMissing(
            UserAddress::TABLE,
            [ 'id' => $address1->id ]
        );
    }

    public function test_it_returns_error_on_deleting_user_address(): void
    {
        $user = $this->loginAsUser();

        /** @var UserAddress $address1 */
        $address1 = UserAddress::factory()->create(['user_id' => $user->id]);

        $this->deleteJson(route('api.v1.mobile.addresses.destroy', [ 'address' => $address1->id + 1 ]))
            ->assertNotFound()
            ->assertJson(['success' => false]);

        self::assertCount(1, $user->addresses);
    }

    public function test_user_can_update_existed_user_address(): void
    {
        $user = $this->loginAsUser();

        /** @var UserAddress $userAddress */
        $userAddress = UserAddress::factory()->create([ 'user_id' => $user->id ]);

        $this->assertDatabaseCount(UserAddress::class, 1);
        $addressData = $this->getAddressData();

        $res = $this->putJson(
                route('api.v1.mobile.addresses.update', [ 'address' => $userAddress->id ]),
                $addressData
            )
            ->assertOk()
            ->assertJsonStructure($this->structureResource([
                'id',
                'region_code',
                'region_name',
                'city_code',
                'city_name',
                'postal_code',
                'address',
                'name',
            ]));

        $address = $res->json('data');

        self::assertEquals($addressData['region_code'], $address['region_code']);
        self::assertEquals($addressData['city_code'], $address['city_code']);
        self::assertEquals($addressData['postal_code'], $address['postal_code']);
        self::assertEquals($addressData['address'], $address['address']);
        self::assertEquals($addressData['name'], $address['name']);

        $this->assertDatabaseHas(
            UserAddress::TABLE,
            [
                'id' => $userAddress->id,
                'user_id' => $user->id,
                'region_code' => $address['region_code'],
                'city_code' => $address['city_code'],
                'postal_code' => $address['postal_code'],
                'address' => $address['address'],
                'name' => $address['name'],
            ]
        );
    }

    public function test_user_can_delete_only_his_own_delivery_address(): void
    {
        $user = $this->loginAsUser();
        /** @var UserAddress $address1 */
        $address1 = UserAddress::factory()->create(['user_id' => $user->id]);

        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var UserAddress $address2 */
        $address2 = UserAddress::factory()->create(['user_id' => $user2->id]);

        $this->deleteJson(route('api.v1.mobile.addresses.destroy', [ 'address' => $address2->id ]))
            ->assertForbidden()
            ->assertJson($this->structureErrorResponse('This action is unauthorized.'));

        self::assertCount(1, $user->addresses);

        $this->assertDatabaseHas(
            UserAddress::TABLE,
            [
                'id' => $address1->id,
                'user_id' => $user->id,
            ]
        );
    }

    public function test_user_can_update_only_his_own_delivery_address(): void
    {
        $user = $this->loginAsUser();
        /** @var UserAddress $address1 */
        UserAddress::factory()->create(['user_id' => $user->id]);

        /** @var User $user2 */
        $user2 = User::factory()->create();
        /** @var UserAddress $address2 */
        $address2 = UserAddress::factory()->create(['user_id' => $user2->id]);

        $addressData = $this->getAddressData();

        $this->putJson(
                route('api.v1.mobile.addresses.update', [ 'address' => $address2->id ]),
                $addressData
            )
            ->assertForbidden()
            ->assertJson($this->structureErrorResponse('This action is unauthorized.'));

        $this->assertDatabaseMissing(
            UserAddress::TABLE,
            [
                'id' => $address2->id,
                'user_id' => $addressData['name'],
            ]
        );
    }

    public function test_it_returns_user_address_array_with_user_profile(): void
    {
        $user = $this->loginAsUser();

        /** @var UserAddress $address1 */
        $address1 = UserAddress::factory()->create(['user_id' => $user->id]);
        /** @var UserAddress $address2 */
        $address2 = UserAddress::factory()->create(['user_id' => $user->id]);

        $res = $this->get(route('api.v1.mobile.user'), $this->headers())
            ->assertOk()
            ->assertJsonStructure($this->structureResource([
                'id',
                'addresses',
            ]));

        $userAddresses = $res->json('data.addresses');

        foreach ([$address1, $address2] as $index => $address) {
            $userAddress = $userAddresses[$index];
            self::assertEquals($address->id, $userAddress['id']);
            self::assertEquals($address->region_code, $userAddress['region_code']);
            self::assertEquals($address->region, $userAddress['region_name']);
            self::assertEquals($address->city_code, $userAddress['city_code']);
            self::assertEquals($address->city, $userAddress['city_name']);
            self::assertEquals($address->postal_code, $userAddress['postal_code']);
            self::assertEquals($address->address, $userAddress['address']);
            self::assertEquals($address->name, $userAddress['name']);
        }
    }

    public function test_it_returns_user_addresses_list(): void
    {
        $user = $this->loginAsUser();

        /** @var UserAddress $address1 */
        $address1 = UserAddress::factory()->create(['user_id' => $user->id]);
        /** @var UserAddress $address2 */
        $address2 = UserAddress::factory()->create(['user_id' => $user->id]);

        $res = $this->get(route('api.v1.mobile.addresses.index'), $this->headers())
            ->assertOk()
            ->assertJsonStructure($this->structureResource([
                '*' => [
                    'id',
                    'name',
                    'region_code',
                    'region_name',
                    'city_code',
                    'city_name',
                    'postal_code',
                    'address',
                ],
            ]));

        $userAddresses = $res->json('data');

        self::assertCount(2, $userAddresses);

        foreach ([$address1, $address2] as $index => $address) {
            $userAddress = $userAddresses[$index];
            self::assertEquals($address->id, $userAddress['id']);
            self::assertEquals($address->region_code, $userAddress['region_code']);
            self::assertEquals($address->region, $userAddress['region_name']);
            self::assertEquals($address->city_code, $userAddress['city_code']);
            self::assertEquals($address->city, $userAddress['city_name']);
            self::assertEquals($address->postal_code, $userAddress['postal_code']);
            self::assertEquals($address->address, $userAddress['address']);
            self::assertEquals($address->name, $userAddress['name']);
        }
    }

    private function getAddressData(): array
    {
        return [
            'region_code' => 299,
            'city_code' => 4756,
            'postal_code' => '030000',
            'name' => 'Дом',
            'address' => 'Ул. Какая-то, 25',
        ];
    }
}
