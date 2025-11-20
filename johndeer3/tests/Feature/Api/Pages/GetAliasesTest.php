<?php

namespace Tests\Feature\Api\Pages;

use App\Models\Page\Page;
use App\Models\User\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class GetAliasesTest extends TestCase
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
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.page.alias-list'))
            ->assertJson($this->structureSuccessResponse([
                Page::ALIAS_AGREEMENT => Page::ALIAS_AGREEMENT,
                Page::ALIAS_PRIVATE_POLICY => Page::ALIAS_PRIVATE_POLICY,
                Page::ALIAS_DISCLAIMER => Page::ALIAS_DISCLAIMER,
            ]))
            ->assertJsonCount(count(Page::aliasList()), 'data')
        ;
    }

    /** @test */
    public function not_admin()
    {
        $user = $this->userBuilder->setRole(
            Role::query()->where('role', Role::ROLE_PSS)->first()
        )->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.page.alias-list'))
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->getJson(route('api.page.alias-list'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


