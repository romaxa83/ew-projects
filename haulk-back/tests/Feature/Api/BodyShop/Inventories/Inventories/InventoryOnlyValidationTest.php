<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Unit;
use Tests\Feature\Api\Orders\OrderTestCase;

class InventoryOnlyValidationTest extends OrderTestCase
{
    public function test_it_validate(): void
    {
        $this->loginAsBodyShopAdmin();

        $formRequest = [
            'name' => '',
            'unit_id' => (factory(Unit::class)->create())->id,
            'purchase' => [
                'quantity' => '',
                /*'cost' => 20.25,
                'invoice_number' => 'SDSASD23324',
                'date' => now()->format('m/d/Y'),*/
            ],
        ];

        $response = $this->postJson(
            route('body-shop.inventories.store'),
            $formRequest,
            [
                config('requestvalidationonly.header_key') => true
            ]
        )
            ->assertOk();
        $content = json_to_array($response->getContent());

        $this->assertCount(2, $content['data']);
    }
}
