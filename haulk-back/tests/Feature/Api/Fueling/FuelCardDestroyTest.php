<?php

namespace Feature\Api\Fueling;

use App\Enums\Fueling\FuelCardStatusEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Orders\Order;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class FuelCardDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users()
    {
        $card = FuelCard::factory()->create();

        $this->deleteJson(route('fuel-cards.destroy', $card))->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users()
    {
        $card = FuelCard::factory()->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('fuel-cards.destroy', $card))
            ->assertForbidden();
    }

    public function test_it_delete_by_super_admin()
    {
        $card = FuelCard::factory()->create();

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $card->getAttributes());

        $this->loginAsCarrierSuperAdmin();
        $this->deleteJson(route('fuel-cards.destroy', $card))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, array_merge(
                $card->getAttributes(),
                [
                    'status' => FuelCardStatusEnum::DELETED
                ]
            )
        );
    }

    public function test_it_delete_by_admin()
    {
        $card = FuelCard::factory()->create();

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $card->getAttributes());

        $this->loginAsCarrierAdmin();
        $this->deleteJson(route('fuel-cards.destroy', $card))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(FuelCard::TABLE_NAME, $card->getAttributes());
    }

    public function test_it_delete_with_related_entities()
    {
        $this->markTestSkipped();
        $this->loginAsCarrierSuperAdmin();

        $card = FuelCard::factory()->create(['type' => FuelCard::TYPE_ORDER]);
        $order = Order::factory()->create();
        $order->tags()->sync([$card->id]);

        $this->deleteJson(route('fuel-cards.destroy', $card))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $card->getAttributes());

        $card = FuelCard::factory()->create(['type' => FuelCard::TYPE_VEHICLE_OWNER]);
        $user = User::factory()->create();
        $user->tags()->sync([$card->id]);

        $this->deleteJson(route('fuel-cards.destroy', $card))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $card->getAttributes());

        $card = FuelCard::factory()->create(['type' => FuelCard::TYPE_TRUCKS_AND_TRAILER]);
        $truck = factory(Truck::class)->create();
        $truck->tags()->sync([$card->id]);

        $this->deleteJson(route('fuel-cards.destroy', $card))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(FuelCard::TABLE_NAME, $card->getAttributes());
    }
}
