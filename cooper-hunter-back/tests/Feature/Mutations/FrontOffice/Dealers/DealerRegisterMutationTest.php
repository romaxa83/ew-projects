<?php

namespace Tests\Feature\Mutations\FrontOffice\Dealers;

use App\Enums\Companies\CompanyStatus;
use App\Events\Dealers\DealerRegisteredEvent;
use App\GraphQL\Mutations\FrontOffice\Dealers\DealerRegisterMutation;
use App\Listeners\Alerts\AlertEventsListener;
use App\Listeners\Dealers\DealerRegisteredListener;
use App\Listeners\Dealers\DealerRegisteredSetRoleListener;
use App\Models\Admins\Admin;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;

class DealerRegisterMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = DealerRegisterMutation::NAME;

    protected DealerBuilder $dealerBuilder;
    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        parent::setUp();

        $this->passportInit();
    }

    /** @test */
    public function register(): void
    {
        Event::fake();
        $admin = Admin::factory()->create();
        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'code' => $this->faker->uuid,
            'status' => CompanyStatus::APPROVE()
        ])->create();

        $this->assertEmpty($company->dealers);
        $this->assertTrue($company->status->isApprove());

        $data = $this->data();
        $data['code'] = $company->code;
        $data['email'] = $company->email;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'refresh_token',
                        'access_expires_in',
                        'refresh_expires_in',
                        'token_type',
                        'access_token'
                    ]
                ]
            ])
        ;

        $company->refresh();

        $this->assertTrue($company->status->isRegister());

        /** @var $dealer Dealer */
        $dealer = $company->dealers[0];

        $this->assertEquals($dealer->email, data_get($data, 'email'));
        $this->assertNotNull($dealer->password);
        $this->assertNull($dealer->first_name);
        $this->assertTrue($dealer->isEmailVerified());
        $this->assertFalse($dealer->isMain());
        $this->assertTrue($dealer->isMainCompany());
        $this->assertEquals($dealer->lang, app('localization')->getDefaultSlug());

        Event::assertDispatched(function (DealerRegisteredEvent $event) use ($dealer) {
            return $event->getDealer()->id === $dealer->id;
        });
        Event::assertListening(DealerRegisteredEvent::class, DealerRegisteredListener::class);
        Event::assertListening(DealerRegisteredEvent::class, AlertEventsListener::class);
        Event::assertListening(DealerRegisteredEvent::class, DealerRegisteredSetRoleListener::class);
    }

    /** @test */
    public function fail_exist_email_member(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'code' => $this->faker->uuid
        ])->create();

        $data = $this->data();
        $data['code'] = $company->code;
        $data['email'] = $company->email;

        Technician::factory()->create([
            'email' => data_get($data, 'email')
        ]);

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => 'validation',
                        'extensions' => [
                            'validation' => [
                                'email' => ["Email is already in use."]
                            ]
                        ]
                    ],
                ]
            ])
        ;

        $this->assertNull(Dealer::query()->where('email', data_get($data, 'email'))->first());
    }

    /** @test */
    public function fail_not_compare_email(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'code' => $this->faker->uuid
        ])->create();

        $data = $this->data();
        $data['code'] = $company->code;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => 'validation',
                        'extensions' => [
                            'validation' => [
                                'email' => [
                                    __('validation.dealer.not_compare_email', [
                                        'email' => data_get($data, 'email'),
                                        'contact_email' => $company->email
                                    ])
                                ]
                            ]
                        ]
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_not_match_code(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->setData([
            'code' => $this->faker->uuid
        ])->create();

        $data = $this->data();
        $data['code'] = '00001';
        $data['email'] = $company->email;

        $this->postGraphQL([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'errors' => [
                    [
                        'message' => 'validation',
                        'extensions' => [
                            'validation' => [
                                'code' => ["The selected code is invalid."]
                            ]
                        ]
                    ],
                ]
            ])
        ;
    }

    public function data(): array
    {

        return [
            'email' => $this->faker->safeEmail,
            'password' => 'password',
        ];
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    code: "%s"
                    email: "%s"
                    password: "%s"
                    password_confirmation: "%s"
                ) {
                    refresh_token
                    access_expires_in
                    refresh_expires_in
                    token_type
                    access_token
                }
            }',
            self::MUTATION,
            data_get($data, 'code'),
            data_get($data, 'email'),
            data_get($data, 'password'),
            data_get($data, 'password'),
        );
    }

}

