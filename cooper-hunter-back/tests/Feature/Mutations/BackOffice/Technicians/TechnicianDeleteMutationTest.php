<?php

namespace Tests\Feature\Mutations\BackOffice\Technicians;

use App\GraphQL\Mutations\BackOffice\Technicians\TechnicianDeleteMutation;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class TechnicianDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public const MUTATION = TechnicianDeleteMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_it_delete_one_model(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianDeletePermission::KEY]);

        $technician = Technician::factory()
            ->create();
        $id = $technician->id;

        $query = $this->getQueryStr($id);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        self::assertNull(Technician::find($id));
    }

    public function test_it_delete_many_model(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianDeletePermission::KEY]);

        $model1 = Technician::factory()->create();
        $model2 = Technician::factory()->create();
        $id1 = $model1->id;
        $id2 = $model2->id;

        $query = $this->getQueryStrTwo($id1, $id2);

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJsonPath('data.'.self::MUTATION, true);

        self::assertNull(Technician::find($id1));
        self::assertNull(Technician::find($id2));
    }

    /** @test */
    public function not_found(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianDeletePermission::KEY]);

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
