<?php

namespace Feature\Http\Api\V1\Customers\CustomerTaxExemption;

use App\Enums\Customers\CustomerTaxExemptionStatus;
use App\Events\Events\Customers\DeclineCustomerTaxExemptionEvent;
use App\Events\Listeners\Customers\SyncEComDeclineCustomerTaxExemptionListener;
use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\TestCase;

class DeclineTest extends TestCase
{
    use DatabaseTransactions;

    protected CustomerBuilder $customerBuilder;
    protected TagBuilder $tagBuilder;

    protected array $data = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->customerBuilder = resolve(CustomerBuilder::class);
    }

    /** @test */
    public function success_decline()
    {
        Event::fake([DeclineCustomerTaxExemptionEvent::class]);

        $this->loginUserAsSuperAdmin();

        $model = $this->customerBuilder->create();
        CustomerTaxExemption::factory()->for($model)->create([
            'status' => CustomerTaxExemptionStatus::UNDER_REVIEW,
            'date_active_to' => null
        ]);
        $id = $this->postJson(route('api.v1.customers.tax-exemption.decline', $model))
            ->assertJsonStructure([
                'data' => [
                    'taxExemption' => [
                        'date_active_to',
                        'file'
                    ]
                ],
            ])->json('data.taxExemption.id');

        $this->assertDatabaseHas(CustomerTaxExemption::TABLE, [
           'status' => CustomerTaxExemptionStatus::DECLINED,
           'date_active_to' => null,
           'customer_id' => $model->id,
        ]);

        Event::assertDispatched(fn (DeclineCustomerTaxExemptionEvent $event) =>
            $event->getModel()->id === (int)$id
        );
        Event::assertListening(DeclineCustomerTaxExemptionEvent::class, SyncEComDeclineCustomerTaxExemptionListener::class);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.tax-exemption.decline', $model));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $model = $this->customerBuilder->create();

        $res = $this->postJson(route('api.v1.customers.tax-exemption.decline', $model));

        self::assertUnauthenticatedMessage($res);
    }
}

