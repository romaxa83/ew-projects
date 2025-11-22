<?php

namespace Tests\Feature\Mutations\BackOffice\Technicians;

use App\GraphQL\Mutations\BackOffice\Technicians\TechnicianSoftDeleteMutation;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class TechnicianSoftDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public const MUTATION = TechnicianSoftDeleteMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_it_one_model_soft_delete(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianSoftDeletePermission::KEY]);

        $technician = Technician::factory()
            ->create();

        $query = $this->getQueryStr($technician->id);

        self::assertNull($technician->deleted_at);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        $technician->refresh();

        self::assertNotNull($technician->deleted_at);
    }

    public function test_it_many_model_soft_delete(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianSoftDeletePermission::KEY]);

        $model1 = Technician::factory()->create();
        $model2 = Technician::factory()->create();

        $query = $this->getQueryStrTwo($model1->id, $model2->id);

        self::assertNull($model1->deleted_at);
        self::assertNull($model2->deleted_at);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        $model1->refresh();
        $model2->refresh();

        self::assertNotNull($model1->deleted_at);
        self::assertNotNull($model2->deleted_at);
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianSoftDeletePermission::KEY]);

        $query = $this->getQueryStr(1);

        $res = $this->postGraphQLBackOffice(compact('query'));

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('validation', $res->json('errors.0.message'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianRestorePermission::KEY]);

        $technician = Technician::factory()->create();

        $query = $this->getQueryStr($technician->id);

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
