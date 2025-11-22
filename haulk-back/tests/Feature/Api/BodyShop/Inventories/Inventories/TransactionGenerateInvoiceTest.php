<?php

namespace Api\BodyShop\Inventories\Inventories;

use App\Models\BodyShop\Inventories\Inventory;
use App\Models\BodyShop\Inventories\Transaction;
use App\Models\Locations\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransactionGenerateInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_generate_invoice(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->fillSettings();

        $inventory = factory(Inventory::class)->create();
        $transaction = factory(Transaction::class)->create([
            'inventory_id' => $inventory->id,
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'describe' => Transaction::DESCRIBE_SOLD,
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Transaction::PAYMENT_METHOD_CHECK,
            'tax' => 3,
            'discount' => 10,
            'first_name' => 'FName',
            'last_name' => 'LName',
            'phone' => '1-323-233-23',
            'email' => 'test@test.com',
        ]);

        $this->getJson(route('body-shop.inventories.generate-invoice', $transaction))
            ->assertOk();
    }

    public function test_generate_payment_receipt(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->fillSettings();

        $inventory = factory(Inventory::class)->create();
        $transaction = factory(Transaction::class)->create([
            'inventory_id' => $inventory->id,
            'operation_type' => Transaction::OPERATION_TYPE_SOLD,
            'describe' => Transaction::DESCRIBE_SOLD,
            'payment_date' => now()->format('m/d/Y'),
            'payment_method' => Transaction::PAYMENT_METHOD_CHECK,
            'tax' => 3,
            'discount' => 10,
            'first_name' => 'FName',
            'last_name' => 'LName',
            'phone' => '1-323-233-23',
            'email' => 'test@test.com',
        ]);

        $this->getJson(route('body-shop.inventories.generate-payment-receipt', $transaction))
            ->assertOk();
    }

    private function fillSettings(): void
    {
        $state = factory(State::class)->create();
        $formRequest = [
            'company_name' => 'Test name',
            'address' => 'test address',
            'city' => 'test city',
            'state_id' => $state->id,
            'zip' => '3454',
            'timezone' => 'America/Los_Angeles',
            'phone' => '3456723455',
            'phone_name' => 'test',
            'phone_extension' => 'ext',
            'phones' => [
                [
                    'name' => 'test 1',
                    'number' => '3443423423',
                    'extension' => '34'
                ],
            ],
            'email' => 'test@test.com',
            'fax' => '35345435',
            'website' => 'test.com',
            'billing_phone' => '435345345345',
            'billing_phone_name' => 'test bill',
            'billing_phone_extension' => 'test bill ext',
            'billing_phones' => [],
            'billing_email' => 'test@test.coom',
            'billing_payment_details' => 'text',
            'billing_terms' => 'terms text',
        ];


        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.settings.update-info'), $formRequest)
            ->assertOk();

        $attributes = [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];
        $this->postJson(route('body-shop.settings.upload-info-photo'), $attributes)
            ->assertOk();
    }
}
