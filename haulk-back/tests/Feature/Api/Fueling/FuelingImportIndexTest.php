<?php

namespace Tests\Feature\Api\Fueling;

use App\Models\Fueling\FuelCard;
use App\Models\Fueling\Fueling;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class FuelingImportIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('fueling-import.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierDriver();

        $this->getJson(route('fueling-import.index'))
            ->assertForbidden();
    }

    public function test_it_all_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $driver = User::factory()->driver()->create();
        $fuelCard = FuelCard::factory()->create();
        Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_transaction_date_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();
        Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => '2025-fgdfgdf',
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['transaction_date_validate_message'], __('validation.transaction_date_validation'));
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_card_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $driver = User::factory()->driver()->create();
        Fueling::factory()->create([
            'valid' => false,
            'user_id' => $driver->id,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertEquals($item['card_validate_message'], __('validation.invalid_card_number'));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }
    public function test_it_driver_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => null,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['user_validate_message'], __('validation.invalid_driver_name'));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_unit_price_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();

        $fueling = Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'valid' => false,
            'card' => 55555,
            'unit_price' => 'test',
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertEquals($item['unit_price_validate_message'], __('validation.invalid_fueling_only_decimals'));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);

        $fueling->unit_price = null;
        $fueling->save();

        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertEquals($item['unit_price_validate_message'], __('validation.required', ['attribute' => 'unit price']));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_location_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();

        Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'location' => null,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['location_validate_message'], __('validation.required', ['attribute' => 'location']));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_state_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();

        Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'state' => null,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['state_validate_message'], __('validation.required', ['attribute' => 'state']));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_fees_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();

        Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'fees' => 'test',
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['fees_validate_message'], __('validation.invalid_fueling_only_decimals'));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }
    public function test_it_item_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();

        $fueling = Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'item' => null,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['item_validate_message'], __('validation.required', ['attribute' => 'item']));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);


        $fueling->item = 12345;
        $fueling->save();

        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['item_validate_message'], __('validation.invalid_fueling_only_text'));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_quantity_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();

        $fueling = Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'quantity' => null,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['quantity_validate_message'], __('validation.required', ['attribute' => 'Quantity']));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['amount_validate_message']);


        $fueling->quantity = 'test';
        $fueling->save();

        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['quantity_validate_message'], __('validation.invalid_fueling_only_decimals'));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['amount_validate_message']);
    }

    public function test_it_amount_not_valid(): void
    {
        $this->loginAsCarrierSuperAdmin();
        $fuelCard = FuelCard::factory()->create();
        $driver = User::factory()->driver()->create();

        $fueling = Fueling::factory()->create([
            'fuel_card_id' => $fuelCard->id,
            'user_id' => $driver->id,
            'amount' => null,
            'valid' => false,
            'card' => 55555,
            'transaction_date' => now(),
        ]);
        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['amount_validate_message'], __('validation.required', ['attribute' => 'amount']));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['quantity_validate_message']);


        $fueling->amount = 'test';
        $fueling->save();

        $response = $this->getJson(route('fueling-import.index'))
            ->assertOk();

        $data = $response['data'];
        $this->assertCount(1, $data);
        $item = array_shift($data);
        $this->assertNull($item['card_validate_message']);
        $this->assertEquals($item['amount_validate_message'], __('validation.invalid_fueling_only_decimals'));
        $this->assertNull($item['transaction_date_validate_message']);
        $this->assertNull($item['user_validate_message']);
        $this->assertNull($item['location_validate_message']);
        $this->assertNull($item['state_validate_message']);
        $this->assertNull($item['fees_validate_message']);
        $this->assertNull($item['unit_price_validate_message']);
        $this->assertNull($item['item_validate_message']);
        $this->assertNull($item['quantity_validate_message']);
    }


    public function test_it_show_all_for_admin(): void
    {
        $this->loginAsCarrierAdmin();

        $this->getJson(route('fueling-import.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_accountant(): void
    {
        $this->loginAsCarrierAccountant();

        $this->getJson(route('fueling-import.index'))
            ->assertOk();
    }
}
