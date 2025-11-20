<?php

namespace Tests\Feature\Api\Template;

use App\Models\Notification\FcmTemplate;
use App\Models\User\Role;
use App\Models\User\User;
use App\Repositories\FcmNotification\FcmNotificationRepository;
use App\Services\FcmNotification\TemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ListTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();
        $templatePostponed = FcmTemplate::query()->where('type', FcmTemplate::POSTPONED)->first();

        $this->getJson(route('api.notification.template.list'))
            ->assertJson($this->structureResource([
                [
                    "id" => $templatePlanned->id,
                    "active" => $templatePlanned->active,
                    "type" => FcmTemplate::PLANNED,
                    "vars" => $templatePlanned->vars,
                    "translations" => [
                        "en" => [
                            "title" => $templatePlanned->translations->where("lang", "en")->first()->title,
                            "text" => $templatePlanned->translations->where("lang", "en")->first()->text,
                        ],
                        "ru" => [
                            "title" => $templatePlanned->translations->where("lang", "ru")->first()->title,
                            "text" => $templatePlanned->translations->where("lang", "ru")->first()->text,
                        ]
                    ]
                ],
                [
                    "id" => $templatePostponed->id,
                    "active" => $templatePostponed->active,
                    "type" => FcmTemplate::POSTPONED,
                    "vars" => $templatePostponed->vars,
                    "translations" => [
                        "en" => [
                            "title" => $templatePostponed->translations->where("lang", "en")->first()->title,
                            "text" => $templatePostponed->translations->where("lang", "en")->first()->text,
                        ],
                        "ru" => [
                            "title" => $templatePostponed->translations->where("lang", "ru")->first()->title,
                            "text" => $templatePostponed->translations->where("lang", "ru")->first()->text,
                        ]
                    ]
                ]
            ]))
            ->assertJsonCount(2, 'data')
        ;
    }

    /** @test */
    public function fail_repo_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(FcmNotificationRepository::class, function(MockInterface $mock){
            $mock->shouldReceive("getAll")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.notification.template.list'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.notification.template.list'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.notification.template.list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
