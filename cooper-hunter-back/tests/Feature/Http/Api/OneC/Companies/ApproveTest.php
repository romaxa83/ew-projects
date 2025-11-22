<?php

namespace Tests\Feature\Http\Api\OneC\Companies;

use App\Events\Companies\UpdateCompanyByOnecEvent;
use App\Listeners\Companies\SendCodeForDealerListener;
use App\Models\Companies\Company;
use App\Models\Companies\Corporation;
use App\Services\Companies\CompanyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Mockery\MockInterface;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class ApproveTest extends TestCase
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
    public function success_approve(): void
    {
        Event::fake([UpdateCompanyByOnecEvent::class]);

        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();

        $this->assertTrue($model->status->isDraft());
        $this->assertNull($model->code);

        $data = [
            'authorization_code' => $this->faker->uuid,
        ];

        $this->postJson(route('1c.companies.approve', ['guid' => $model->guid]), $data)
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertTrue($model->status->isApprove());
        $this->assertEquals($model->code, data_get($data, 'authorization_code'));

        Event::assertDispatched(function (UpdateCompanyByOnecEvent $event) use ($model) {
            return $event->getCompany()->id === $model->id;
        });
        Event::assertListening(UpdateCompanyByOnecEvent::class, SendCodeForDealerListener::class);
    }

    /** @test */
    public function company_has_code(): void
    {
        Event::fake([UpdateCompanyByOnecEvent::class]);

        $this->loginAsModerator();

        $code = $this->faker->uuid;
        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid,
            'code' => $code
        ])->create();

        $this->assertTrue($model->status->isDraft());

        $data = [
            'authorization_code' => $code,
        ];

        $this->postJson(route('1c.companies.approve', ['guid' => $model->guid]), $data)
            ->assertJson([
                'errors' => [
                    [
                        'key' => 'authorization_code',
                        'messages' => ['The authorization code has already been taken.']
                    ]
                ],
            ]);

    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $model Company */
        $model = $this->companyBuilder->setData([
            'guid' => $this->faker->uuid,
        ])->create();

        $this->mock(CompanyService::class, function(MockInterface $mock){
            $mock->shouldReceive("approveCompany")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('1c.companies.approve', ['guid' => $model->guid]), [])
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }
}

