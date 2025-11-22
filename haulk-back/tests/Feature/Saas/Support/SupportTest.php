<?php

namespace Tests\Feature\Saas\Support;

use App\Broadcasting\Events\Support\Backoffice\ChangeRequestStatusBroadcast as ChangeRequestStatusBroadcastBackoffice;
use App\Broadcasting\Events\Support\Backoffice\NewIsNotReadMessageBroadcast as NewIsNotReadMessageBroadcastBackoffice;
use App\Broadcasting\Events\Support\Backoffice\NewIsNotViewRequestBroadcast;
use App\Broadcasting\Events\Support\Backoffice\NewMessageBroadcast as NewMessageBroadcastBackoffice;
use App\Broadcasting\Events\Support\Backoffice\NewRequestBroadcast as NewRequestBroadcastBackoffice;
use App\Broadcasting\Events\Support\Crm\ChangeRequestStatusBroadcast as ChangeRequestStatusBroadcastCrm;
use App\Broadcasting\Events\Support\Crm\NewIsNotReadMessageBroadcast as NewIsNotReadMessageBroadcastCrm;
use App\Broadcasting\Events\Support\Crm\NewMessageBroadcast as NewMessageBroadcastCrm;
use App\Broadcasting\Events\Support\Crm\NewRequestBroadcast as NewRequestBroadcastCrm;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Saas\Support\SupportRequestMessage;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;


class SupportTest extends TestCase
{

    use DatabaseTransactions;

    private User $userSupporter;

    private SupportRequest $supportRequest;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_list_statuses(): void
    {
        $this->loginAsSaasSuperAdmin();

        $this->getJson(
            route('v1.saas.support.statuses')
        )
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(count(SupportRequest::STATUSES_DESCRIPTION), 'data');
    }

    public function test_get_list_labels(): void
    {
        $this->loginAsSaasSuperAdmin();

        $response = $this->getJson(route('v1.saas.support.labels'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(count(SupportRequest::LABELS_DESCRIPTION), 'data');

        $last = Arr::last($response->json('data'));

        $this->assertEquals($last['id'], SupportRequest::LABEL_OTHER);
    }

    public function test_get_list_sources(): void
    {
        $this->loginAsSaasSuperAdmin();

        $response = $this->getJson(route('v1.saas.support.sources'));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['id', 'name']]])
            ->assertJsonCount(count(SupportRequest::SOURCES_DESCRIPTION), 'data');
    }

    private function createSupportRequest(bool $withoutUser = false): TestResponse
    {
        if ($withoutUser === false) {
            $this->userSupporter = $this->loginAsCarrierDriver();
        }

        $response = $this->postJson(
            route('v1.saas.support.crm.store'),
            [
                'user_name' => $this->faker->name,
                'user_email' => $this->faker->email,
                'user_phone' => $this->faker->e164PhoneNumber,
                'subject' => $this->faker->text(100),
                'message' => $this->faker->text,
                'attachments' => [
                    UploadedFile::fake()->create('image.jpeg')
                ]
            ]
        )
            ->assertCreated();

        if ($withoutUser === false) {
            $response->assertJsonStructure([
                'data' => [
                    "id",
                    "status" => [
                        "id",
                        "name",
                    ],
                    "created_at",
                    "author" => [
                        "id",
                        "full_name",
                        "email",
                        "phone",
                        "photo",
                        "role" => [
                            "id",
                            "name",
                        ],
                        "is_support_employee",
                    ],
                    "subject",
                    "message",
                    "attachments" => [
                        [
                            "id",
                            "name",
                            "file_name",
                            "mime_type",
                            "url",
                            "size",
                            "created_at",
                        ]
                    ],
                    "closed_at",
                    "closed_by",
                ]
            ]);

            $data = $response->json('data');
            $this->assertNotNull($data['author']);
            $this->assertEquals(SupportRequest::STATUS_NEW, $data['status']['id']);

            $this->assertDatabaseHas(
                SupportRequest::class,
                [
                    'id' => $data['id']
                ]
            );

            $this->assertDatabaseHas(
                SupportRequestMessage::class,
                [
                    'support_request_id' => $data['id']
                ]
            );

            $this->supportRequest = SupportRequest::find($data['id']);
        } else {
            $this->supportRequest = SupportRequest::whereNull('user_id')->get()->first();
        }

        return $response;
    }

    public function test_create_answer_close_new_support_request(): void
    {
        Event::fake();
        $this->createSupportRequest();

        $this->addMessageInSupportRequest()->assertCreated()->assertJsonStructure([
            'data' => [
                "id",
                "message",
                "created_at",
                "author" => [
                    "id",
                    "full_name",
                    "email",
                    "phone",
                    "photo",
                    "role" => [
                        "id",
                        "name"
                    ],
                    "is_support_employee"
                ],
                "attachments"
            ]
        ]);

        $this->putJson(
            route('v1.saas.support.crm.close', $this->supportRequest)
        )
            ->assertOk()
            ->json('data');

        Event::assertDispatched(NewRequestBroadcastBackoffice::class);
        Event::assertDispatched(NewRequestBroadcastCrm::class);
        Event::assertDispatched(NewMessageBroadcastBackoffice::class, 2);
        Event::assertDispatched(NewMessageBroadcastCrm::class, 2);
        Event::assertDispatched(NewIsNotViewRequestBroadcast::class, 2);
        Event::assertDispatched(ChangeRequestStatusBroadcastCrm::class);
        Event::assertDispatched(ChangeRequestStatusBroadcastBackoffice::class);
    }

    public function test_read_answer_close_support_request_other_user(): void
    {
        $this->createSupportRequest();

        $this->loginAsCarrierDispatcher();

        $response = $this->getJson(route('v1.saas.support.crm.index'))
            ->assertOk()
            ->json('data');

        $this->assertCount(1, $response);
        $this->assertEquals($this->supportRequest->id, $response[0]['id']);

        $response = $this->getJson(route('v1.saas.support.crm.show', $this->supportRequest))
            ->assertOk()
            ->json('data');

        $this->addMessageInSupportRequest()->assertCreated();
        $this->putJson(
            route('v1.saas.support.crm.close', $this->supportRequest)
        )->assertForbidden();
    }

    public function test_read_answer_close_support_request_other_admin_user(): void
    {
        $this->createSupportRequest();

        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('v1.saas.support.crm.show', $this->supportRequest))
            ->assertOk()
            ->json('data');

        $this->addMessageInSupportRequest()->assertCreated()->assertJsonStructure([
            'data' => [
                "id",
                "message",
                "created_at",
                "author" => [
                    "id",
                    "full_name",
                    "email",
                    "phone",
                    "photo",
                    "role" => [
                        "id",
                        "name"
                    ],
                    "is_support_employee"
                ],
                "attachments"
            ]
        ]);
        $this->putJson(
            route('v1.saas.support.crm.close', $this->supportRequest)
        )->assertOk();
    }

    private function addMessageInSupportRequest($type = 'crm'): TestResponse
    {
        return $this->postJson(
            route('v1.saas.support.' . $type . '.store-message', $this->supportRequest),
            [
                'message' => $this->faker->text
            ]
        );
    }

    public function test_get_requests_from_other_company(): void
    {
        $this->createSupportRequest();

        $this->logout();

        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.trial.slug'));
        /** @var User $user */
        $user = User::factory()->create(
            [
                'carrier_id' => $company->id,
            ]
        )->assignRole(User::SUPERADMIN_ROLE);

        $this->loginAsCarrierSuperAdmin($user);

        $response = $this->getJson(route('v1.saas.support.crm.index'))
            ->assertOk()->json('data');

        $this->assertEmpty($response);

        $this->getJson(route('v1.saas.support.crm.show', $this->supportRequest))
            ->assertNotFound();

        $this->addMessageInSupportRequest()->assertNotFound();
    }

    public function test_get_requests_from_backoffice(): void
    {
        $this->createSupportRequest();

        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.support.back-office.index'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        "author" => [
                            "id",
                            "full_name",
                            "email",
                            "phone",
                            "photo",
                            "role" => [
                                "id",
                                "name"
                            ],
                            "is_support_employee"
                        ],
                        'company' => [
                            'id',
                            'name'
                        ],
                        'status' => [
                            'id',
                            'name'
                        ],
                        'label',
                        'source' => [
                            'id',
                            'name'
                        ],
                        'created_at',
                        "closed_at",
                        "closed_by",
                        'subject',
                        'message',
                        "attachments",
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_add_admin_answer_before_take(): void
    {
        $this->createSupportRequest();

        $this->loginAsSaasSuperAdmin();

        $this->addMessageInSupportRequest('back-office')->assertForbidden();
    }

    public function test_can_closed_admin_before_take(): void
    {
        $this->createSupportRequest();

        $this->loginAsSaasSuperAdmin();

        $this->addMessageInSupportRequest('back-office')->assertForbidden();
    }

    public function test_view_request_to_admin(): void
    {
        $this->createSupportRequest();

        $admin = $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.support.back-office.show', $this->supportRequest))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    "author" => [
                        "id",
                        "full_name",
                        "email",
                        "phone",
                        "photo",
                        "role" => [
                            "id",
                            "name"
                        ],
                        "is_support_employee"
                    ],
                    'company' => [
                        'id',
                        'name'
                    ],
                    'status' => [
                        'id',
                        'name'
                    ],
                    'label',
                    'source' => [
                        'id',
                        'name'
                    ],
                    'created_at',
                    "closed_at",
                    "closed_by",
                    'subject',
                    'message',
                    "attachments",
                ]
            ])->json('data');

        $this->assertDatabaseHas(
            SupportRequest::class,
            [
                'id' => $this->supportRequest->id,
                'viewed->0' => $admin->id
            ]
        );

        $this->loginAsCarrierSuperAdmin();

        $this->addMessageInSupportRequest();

        $this->assertDatabaseMissing(
            SupportRequest::class,
            [
                'id' => $this->supportRequest->id,
                'viewed->0' => $admin->id
            ]
        );
    }

    public function test_take_request(): void
    {
        $this->createSupportRequest();

        $this->loginAsSaasSuperAdmin();

        $this->getJson(route('v1.saas.support.back-office.show', $this->supportRequest));

        Event::fake();

        $response = $this->putJson(
            route('v1.saas.support.back-office.take', $this->supportRequest)
        )
            ->assertOk()
            ->json('data');

        Event::assertDispatched(ChangeRequestStatusBroadcastBackoffice::class);
        Event::assertDispatched(ChangeRequestStatusBroadcastCrm::class);

        $this->assertNotNull($response['author']);
        $this->assertEquals(SupportRequest::STATUS_IN_WORK, $response['status']['id']);
    }

    public function test_close_request_by_admin(): void
    {
        $this->createSupportRequest();

        $this->loginAsSaasSuperAdmin();

        $this->putJson(
            route('v1.saas.support.back-office.take', $this->supportRequest)
        );

        $this->putJson(
            route('v1.saas.support.back-office.close', $this->supportRequest)
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.source.parameter', 'closing_reason');

        $this->putJson(
            route('v1.saas.support.back-office.close', $this->supportRequest),
            [
                'closing_reason' => $this->faker->text
            ]
        )
            ->assertOk();

        $this->supportRequest->refresh();

        $this->assertNotNull($this->supportRequest->closed_at);
        $this->assertNotNull($this->supportRequest->closed_by);
        $this->assertTrue($this->supportRequest->closed_by_support_employee);
        $this->assertNotNull($this->supportRequest->closing_reason);
    }

    public function test_chatting(): void
    {
        $this->createSupportRequest();

        $admin = $this->loginAsSaasSuperAdmin();

        $this->putJson(route('v1.saas.support.back-office.take', $this->supportRequest));

        Event::fake();

        $this->addMessageInSupportRequest('back-office');
        $this->addMessageInSupportRequest('back-office');

        Event::assertDispatched(NewMessageBroadcastCrm::class, 2);
        Event::assertDispatched(NewMessageBroadcastBackoffice::class, 2);
        Event::assertDispatched(NewIsNotReadMessageBroadcastCrm::class, 2);

        $this->loginAsCarrierDriver($this->userSupporter);

        $this->getJson(route('v1.saas.support.crm.show', $this->supportRequest))->json('data');

        $this->loginAsCarrierSuperAdmin();

        Event::fake();

        $this->addMessageInSupportRequest();

        Event::assertDispatched(NewMessageBroadcastCrm::class);
        Event::assertDispatched(NewMessageBroadcastBackoffice::class);
        Event::assertDispatched(NewIsNotReadMessageBroadcastBackoffice::class);

        $this->loginAsSaasSuperAdmin($admin);

        Event::fake();

        $this->addMessageInSupportRequest('back-office');

        Event::assertDispatched(NewMessageBroadcastCrm::class);
        Event::assertDispatched(NewMessageBroadcastBackoffice::class);
        Event::assertDispatched(NewIsNotReadMessageBroadcastCrm::class, 2);
    }

    public function test_create_support_request_from_landing(): void
    {
        $this->createSupportRequest(true);

        $this->assertNull($this->supportRequest->user_id);

        $this->assertEquals(SupportRequest::SOURCE_LANDING, $this->supportRequest->source);

        $this->loginAsSaasSuperAdmin();

        $this->putJson(route('v1.saas.support.back-office.take', $this->supportRequest))
            ->assertOk();
    }
}
