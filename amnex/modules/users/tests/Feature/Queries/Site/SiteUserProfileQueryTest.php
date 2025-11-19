<?php

namespace Wezom\Users\Tests\Feature\Queries\Site;

use Illuminate\Testing\TestResponse;
use Wezom\Core\Testing\TestCase;
use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Tests\GraphQL\Projections\Users\UserProjection;
use Wezom\Users\Traits\UserTestTrait;

class SiteUserProfileQueryTest extends TestCase
{
    use UserTestTrait;

    public function testGuestCantGetProfile(): void
    {
        $result = $this->executeQuery();

        $this->assertGraphQlUnauthorized($result);
    }

    public function testUserCanGetOwnProfile(): void
    {
        $user = UserFactory::new()->verified()->create();

        $this->loginAsUser($user);

        $profile = $this->executeQuery()
            ->assertNoErrors()
            ->assertJson(
                [
                    'data' => [
                        $this->operationName() => [
                            'id' => $user->id,
                            'email' => $user->email,
                            'firstName' => $user->first_name,
                            'lastName' => $user->last_name,
                        ],
                    ]
                ]
            )
            ->json('data.' . $this->operationName());

        self::assertNotEmpty($profile['emailVerifiedAt']);
    }

    private function executeQuery(): TestResponse
    {
        return $this->query()
            ->select(UserProjection::root())
            ->executeAndReturnResponse();
    }
}
