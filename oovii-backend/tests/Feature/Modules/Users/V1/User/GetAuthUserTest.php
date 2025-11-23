<?php

namespace Tests\Feature\Modules\Users\V1\User;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;
use WezomCms\Users\Models\User;

class GetAuthUserTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success(): void
    {
        $user = $this->loginAsUser();

        $this->get(route('api.v1.mobile.user'), $this->headers())
            ->assertOk()
            ->assertJson($this->structureResource([
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'phone' => $user->phone,
                'email' => $user->email,
            ]));
    }

    public function test_it_returns_ref_id_and_bonus_user_properties(): void
    {
        $user = $this->loginAsUser();

        /** @var User $inviter */
        $inviter = User::factory()->create();
        $user->ref_id = $inviter->id;
        $user->bonus = 285;
        $user->save();

        $this->get(route('api.v1.mobile.user'), $this->headers())
            ->assertOk()
            ->assertJson($this->structureResource([
                'id' => $user->id,
                'phone' => $user->phone,
                'ref_id' => $user->ref_id,
                'bonus' => $user->bonus,
            ]));
    }

    /** @test */
    public function not_auth(): void
    {
        $this->get(route('api.v1.mobile.user'), $this->headers())
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse(__("cms-core::site.Unauthenticated")));
    }
}
