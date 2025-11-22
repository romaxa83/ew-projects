<?php

namespace Feature\Http\Api\V1\Customers\CustomerTaxExemption;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Events\Events\Customers\DeleteCustomerTaxExemptionEvent;
use App\Events\Listeners\Customers\SyncEComDeleteCustomerTaxExemptionListener;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        Event::fake([DeleteCustomerTaxExemptionEvent::class]);

        $this->loginUserAsSuperAdmin();

        $model = $this->customerBuilder->create();
        CustomerTaxExemption::factory()->for($model)->create([
            'status' => CustomerTaxExemptionStatus::UNDER_REVIEW,
            'date_active_to' => null
        ]);
        $this->deleteJson(route('api.v1.customers.tax-exemption.delete', $model))
            ->assertJson([
                'data' => [
                    'taxExemption' => null
                ],
            ]);

        $this->assertDatabaseCount(CustomerTaxExemption::TABLE, 0);

        Event::assertDispatched(fn (DeleteCustomerTaxExemptionEvent $event) =>
            $event->getModel()->id === (int)$model->id
        );
        Event::assertListening(DeleteCustomerTaxExemptionEvent::class, SyncEComDeleteCustomerTaxExemptionListener::class);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $model = $this->customerBuilder->create();

        $res = $this->deleteJson(route('api.v1.customers.tax-exemption.delete', $model));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $model = $this->customerBuilder->create();

        $res = $this->deleteJson(route('api.v1.customers.tax-exemption.delete', $model));

        self::assertUnauthenticatedMessage($res);
    }
}

