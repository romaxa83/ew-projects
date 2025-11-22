<?php

namespace Tests\Feature\Mutations\BackOffice\Users;

use App\GraphQL\Mutations\BackOffice\Users\UserSoftDeleteMutation;
use App\Models\Users\User;
use App\Permissions\Users;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class UserSoftDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public const MUTATION = UserSoftDeleteMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_it_soft_delete_one(): void
    {
        $this->loginByAdminManager([Users\UserSoftDeletePermission::KEY]);

        $model = User::factory()
            ->create();

        $query = $this->getQueryStr($model->id);

        self::assertNull($model->deleted_at);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertJsonPath('data.'.self::MUTATION, true);

        $model->refresh();

        self::assertNotNull($model->deleted_at);
    }

    public function test_it_soft_delete_many(): void
    {
        $this->loginByAdminManager([Users\UserSoftDeletePermission::KEY]);

        $model1 = User::factory()->create();
        $model2 = User::factory()->create();

        $query = $this->getQueryStrTwo($model1->id, $model2->id);

        self::assertNull($model1->deleted_at);
        self::assertNull($model2->deleted_at);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertJsonPath('data.'.self::MUTATION, true);

        $model1->refresh();
        $model2->refresh();

        self::assertNotNull($model1->deleted_at);
        self::assertNotNull($model2->deleted_at);
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Users\UserSoftDeletePermission::KEY]);

        $query = $this->getQueryStr(1);

        $res = $this->postGraphQLBackOffice(compact('query'));

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Users\UserRestorePermission::KEY]);

        $model = User::factory()->create();

        $query = $this->getQueryStr($model->id);

        $res = $this->postGraphQLBackOffice(compact('query'));

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    public function getQueryStr($id): string
    {
        return sprintf(
            'mutation { %s (
                    ids: [%d]
                )
            }',
            self::MUTATION,
            $id
        );
    }

    public function getQueryStrTwo($id1, $id2): string
    {
        return sprintf(
            'mutation { %s (
                    ids: [%d, %d]
                )
            }',
            self::MUTATION,
            $id1, $id2
        );
    }
}

