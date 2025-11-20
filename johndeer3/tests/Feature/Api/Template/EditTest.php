<?php

namespace Tests\Feature\Api\Template;

use App\Models\Notification\FcmTemplate;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\FcmNotification\TemplateService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class EditTest extends TestCase
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

        $data = [
            "translations" => [
                "ru" => [
                    "title" => "some title ru",
                    "text" => "some text ru",
                ],
                "en" => [
                    "title" => "some title en",
                    "text" => "some text en",
                ]
            ]
        ];

        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();

        $this->assertNotEquals(
            data_get($data, "translations.ru.title"),
            $templatePlanned->translations->where("lang", "ru")->first()->title
        );
        $this->assertNotEquals(
            data_get($data, "translations.ru.text"),
            $templatePlanned->translations->where("lang", "ru")->first()->text
        );
        $this->assertNotEquals(
            data_get($data, "translations.en.title"),
            $templatePlanned->translations->where("lang", "en")->first()->title
        );
        $this->assertNotEquals(
            data_get($data, "translations.en.text"),
            $templatePlanned->translations->where("lang", "en")->first()->text
        );

        $this->postJson(route('api.notification.template.edit', [
            "template" => $templatePlanned->id
        ]), $data)
            ->assertJson($this->structureResource([
                "id" => $templatePlanned->id,
                "active" => $templatePlanned->active,
                "type" => FcmTemplate::PLANNED,
                "vars" => $templatePlanned->vars,
                "translations" => [
                    "en" => [
                        "title" => data_get($data, "translations.en.title"),
                        "text" => data_get($data, "translations.en.text"),
                    ],
                    "ru" => [
                        "title" => data_get($data, "translations.ru.title"),
                        "text" => data_get($data, "translations.ru.text"),
                    ]
                ]
            ]))
            ->assertJsonCount(2, "data.translations")
        ;
    }

    /** @test */
    public function success_add_new_language()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [
            "translations" => [
                "de" => [
                    "title" => "some title de",
                    "text" => "some text de",
                ]
            ]
        ];

        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();

        $this->assertCount(2, $templatePlanned->translations);
        $this->assertNull(
            $templatePlanned->translations->where("lang", "de")->first()
        );

        $this->postJson(route('api.notification.template.edit', [
            "template" => $templatePlanned->id
        ]), $data)
            ->assertJson($this->structureResource([
                "id" => $templatePlanned->id,
                "active" => $templatePlanned->active,
                "type" => FcmTemplate::PLANNED,
                "vars" => $templatePlanned->vars,
                "translations" => [
                    "de" => [
                        "title" => data_get($data, "translations.de.title"),
                        "text" => data_get($data, "translations.de.text"),
                    ]
                ]
            ]))
            ->assertJsonCount(3, "data.translations")
        ;
    }

    /** @test */
    public function success_wrong_language_slug()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = [
            "translations" => [
                "wrong" => [
                    "title" => "some title de",
                    "text" => "some text de",
                ]
            ]
        ];

        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();

        $this->assertCount(2, $templatePlanned->translations);
        $this->assertNull(
            $templatePlanned->translations->where("lang", "wrong")->first()
        );

        $this->postJson(route('api.notification.template.edit', [
            "template" => $templatePlanned->id
        ]), $data)
            ->assertJsonCount(2, "data.translations")
        ;

        $templatePlanned->refresh();

        $this->assertNull(
            $templatePlanned->translations->where("lang", "wrong")->first()
        );
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(TemplateService::class, function(MockInterface $mock){
            $mock->shouldReceive("edit")
                ->andThrows(\Exception::class, "some exception message");
        });

        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();

        $this->postJson(route('api.notification.template.edit', [
            "template" => $templatePlanned->id
        ]), [])
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

        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();

        $this->postJson(route('api.notification.template.edit', [
            "template" => $templatePlanned->id
        ]), [])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();

        $this->postJson(route('api.notification.template.edit', [
            "template" => $templatePlanned->id
        ]), [])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
