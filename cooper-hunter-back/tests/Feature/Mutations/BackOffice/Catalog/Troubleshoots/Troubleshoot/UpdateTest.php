<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootUpdateMutation;
use App\Models\Catalog\Troubleshoots;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Traits\Storage\TestStorage;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    use TestStorage;
    use AdminManagerHelperTrait;
    use WithFaker;

    private Troubleshoots\Troubleshoot $troubleshoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Troubleshoot\UpdatePermission::KEY]);
        $this->troubleshoot = Troubleshoots\Troubleshoot::factory()->create();
    }

    /**
     * @throws FileNotFoundException
     */
    public function test_success(): void
    {
        $group = Group::factory()->create();
        $name = $this->faker->name;
        $pdf = $this->getSamplePdf();

        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(TroubleshootUpdateMutation::NAME)
                ->args([
                    'id' => $this->troubleshoot->id,
                    'active' => false,
                    'group_id' => $group->id,
                    'name' => $name,
                    'pdf' => $pdf
                ])
                ->select([
                    'id',
                    'active',
                    'name',
                    'group' => [
                        'id',
                    ],
                    'pdf' => [
                        'file_name'
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    TroubleshootUpdateMutation::NAME => [
                        'id' => $this->troubleshoot->id,
                        'active' => false,
                        'name' => $name,
                        'group' => [
                            'id' => $group->id
                        ],
                        'pdf' => [
                            'file_name' => $pdf->getClientOriginalName()
                        ]
                    ]
                ]
            ]);
    }
}

