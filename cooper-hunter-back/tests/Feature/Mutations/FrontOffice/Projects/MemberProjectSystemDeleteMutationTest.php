<?php

namespace Tests\Feature\Mutations\FrontOffice\Projects;

use App\GraphQL\Mutations\FrontOffice\Projects\MemberProjectSystemDeleteMutation;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Projects\Project;
use App\Models\Projects\System;
use App\Models\Users\User;
use App\Rules\Projects\SystemBelongsToMemberRule;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\ProjectCreateTrait;

class MemberProjectSystemDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use ProjectCreateTrait;

    public const MUTATION = MemberProjectSystemDeleteMutation::NAME;

    public function test_member_can_delete_own_project_system(): void
    {
        $project = $this->createProjectForMember(
            $this->loginAsUserWithRole()
        );

        /** @var System $system */
        $system = $project->systems->first();

        /** @var Product $unit */
        $unit = $system->units->first();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $system->id
            ]
        );

        $this->assertDatabaseHas(Project::TABLE, ['id' => $project->id]);
        $this->assertDatabaseHas(System::TABLE, ['id' => $system->id]);
        $this->assertDatabaseHas(SystemUnitPivot::TABLE, [
            'system_id' => $system->id,
            'product_id' => $unit->id,
        ]);

        $this->postGraphQL($query->getMutation())
            ->assertOk();

        $this->assertDatabaseHas(Project::TABLE, ['id' => $project->id]);
        $this->assertDatabaseMissing(System::TABLE, ['id' => $system->id]);
        $this->assertDatabaseMissing(SystemUnitPivot::TABLE, [
            'system_id' => $system->id,
            'product_id' => $unit->id,
        ]);
    }

    public function test_member_can_not_delete_foreign_project_system(): void
    {
        $this->loginAsUserWithRole();

        $project = $this->createProjectForMember(
            $user = User::factory()->create()
        );

        /** @var System $system */
        $system = $project->systems->first();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $system->id
            ]
        );

        $this->assertResponseHasValidationMessage(
            $this->postGraphQL($query->getMutation()),
            'id',
            [
                (new SystemBelongsToMemberRule($user))->message()
            ]
        );
    }
}
