<?php

namespace Tests\Feature\Api\User\Site;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ChangeLanguageTest extends TestCase
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
        list($ua, $en) = ['ua', 'en'];
        $user = $this->userBuilder->setLang($ua)->create();
        $this->loginAsUser($user);

        $this->assertEquals($user->lang, $ua);

        $data = ['lang' => $en];

        $this->postJson(route('api.user.change-language'), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $user->id,
                'lang' => $en,
            ]))
        ;

        $user->refresh();
        $this->assertEquals($user->lang, $en);
    }

    /** @test */
    public function fail_without_lang()
    {
        list($ua, $en) = ['ru', 'en'];
        $user = $this->userBuilder->setLang($ua)->create();
        $this->loginAsUser($user);

        $this->assertEquals($user->lang, $ua);

        $data = [];

        $this->postJson(route('api.user.change-language'), $data)
            ->assertJson($this->structureErrorResponse([
                __('validation.required', ['attribute' => 'lang'])
            ]))
        ;
    }

    /** @test */
    public function fail_wrong_lang()
    {
        list($ua, $wrong) = ['ua', 'wrong'];
        $user = $this->userBuilder->setLang($ua)->create();
        $this->loginAsUser($user);

        $this->assertEquals($user->lang, $ua);

        $data = ['lang' => $wrong];

        $this->postJson(route('api.user.change-language'), $data)
            ->assertJson($this->structureErrorResponse(__('message.language_not_exists',['lang' => $wrong])))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->userBuilder->create();

        $data = ['lang' => 'en'];

        $this->postJson(route('api.user.change-language'), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

