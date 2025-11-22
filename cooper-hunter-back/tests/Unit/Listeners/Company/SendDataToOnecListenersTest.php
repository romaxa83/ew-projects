<?php

namespace Tests\Unit\Listeners\Company;

use App\Events\Companies\CreateOrUpdateCompanyEvent;
use App\Listeners\Companies\SendDataToOnecListeners;
use App\Models\Companies\Company;
use App\Models\Companies\ShippingAddress;
use App\Models\Request\Request;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class SendDataToOnecListenersTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_create()
    {
        /** @var $model Company */
        $model = $this->companyBuilder->withContacts()->create();

        ShippingAddress::factory()->create(
            [
                'company_id' => $model,
                'fax' => null
            ]
        );

        $this->assertNull(Request::first());

        $event = new CreateOrUpdateCompanyEvent($model);
        $listener = resolve(SendDataToOnecListeners::class);
        $listener->handle($event);

        $record = Request::first();

        $this->assertTrue($record->command->isCreateCompany());
    }

    /** @test */
    public function success_update()
    {
        /** @var $model Company */
        $model = $this->companyBuilder->withContacts()->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->assertNull(Request::first());

        $event = new CreateOrUpdateCompanyEvent($model);
        $listener = resolve(SendDataToOnecListeners::class);
        $listener->handle($event);

        $record = Request::first();

        $this->assertTrue($record->command->isUpdateCompany());
    }

    /** @test */
    public function something_wrong()
    {
        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->mock(RequestService::class, function(MockInterface $mock){
            $mock->shouldReceive("updateCompany")
                ->andThrows(\Exception::class, "some exception message");
        });

        $event = new CreateOrUpdateCompanyEvent($model);
        $listener = resolve(SendDataToOnecListeners::class);

        $this->expectException(TranslatedException::class);
        $this->expectExceptionMessage("some exception message");
        $this->expectExceptionCode(502);

        $listener->handle($event);
    }
}
