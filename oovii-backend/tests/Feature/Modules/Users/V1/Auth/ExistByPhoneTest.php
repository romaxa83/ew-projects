<?php

namespace Tests\Feature\Modules\Users\V1\Auth;

use App;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ExistByPhoneTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    private $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $localeRu = 'ru';
        $localeKk = 'kk';
        $phone = '+380955545453';
        $user = $this->userBuilder->setPhone($phone)->create();
        $this->loginAsUser($user);

        self::assertEquals($localeRu, App::getLocale());

        $this->postJson(
            route('api.v1.mobile.user.exist-by-phone'),
            ['phone' => $phone],
            ['Content-Language' => $localeKk]
        )->assertOk()
            ->assertJson($this->structureSucessResponse(
                __('cms-users::admin.message.user exist')
            ));

        self::assertEquals($localeKk, App::getLocale());
    }

    /** @test */
    public function fail_not_user(): void
    {
        $phone = '+380955545453';
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->postJson(
            route('api.v1.mobile.user.exist-by-phone'),
            ['phone' => $phone]
        )->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson($this->structureErrorResponse(
                __('cms-users::admin.exception.Not found user by phone', [
                    'phone' => $phone
                ])
            ));
    }
}

