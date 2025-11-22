<?php

namespace Tests\Feature\Http\Api\V1\Customers\CustomerTaxExemption;

use App\Events\Events\Customers\CreateCustomerTaxExemptionEvent;
use App\Events\Listeners\Customers\SyncEComCreateCustomerTaxExemptionListener;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->customerBuilder = resolve(CustomerBuilder::class);

        $this->data = [
            'date_active_to' => now()->addMonths(2)->format('m/d/Y'),
            'file' => UploadedFile::fake()->image('img.png'),
        ];
    }

    /** @test */
    public function success_create()
    {
        Event::fake([CreateCustomerTaxExemptionEvent::class]);

        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $model = $this->customerBuilder->create();

        $id = $this->postJson(route('api.v1.customers.tax-exemption.store', $model), $data)
            ->assertJsonStructure([
                'data' => [
                    'taxExemption' => [
                        'date_active_to',
                        'file'
                    ]
                ],
            ])->json('data.taxExemption.id');

        $this->assertDatabaseHas(CustomerTaxExemption::TABLE, [
           'date_active_to' => $this->data['date_active_to'],
           'customer_id' => $model->id,
        ]);

        Event::assertDispatched(fn (CreateCustomerTaxExemptionEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(CreateCustomerTaxExemptionEvent::class, SyncEComCreateCustomerTaxExemptionListener::class);
    }

    /** @test */
    public function field_success_with_validate_only()
    {
        $this->loginUserAsSuperAdmin();

        $data = $this->data;
        $model = $this->customerBuilder->create();

        $this->postJson(route('api.v1.customers.tax-exemption.store', $model), $data, [
            'Validate-Only' => true
        ])
            ->assertJsonCount(0, 'data');

        $this->assertDatabaseEmpty(CustomerTaxExemption::TABLE);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $data = $this->data;
        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.tax-exemption.store', $model), $data);

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $data = $this->data;
        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.tax-exemption.store', $model), $data);

        self::assertUnauthenticatedMessage($res);
    }
}

