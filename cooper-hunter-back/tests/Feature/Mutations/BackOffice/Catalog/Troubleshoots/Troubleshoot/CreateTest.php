<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootCreateMutation;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use Tests\Traits\Storage\TestStorage;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use TestStorage;
    use AdminManagerHelperTrait;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Troubleshoot\CreatePermission::KEY]);

        $this->fakeMediaStorage();
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
            GraphQLQuery::upload(TroubleshootCreateMutation::NAME)
                ->args([
                    'active' => true,
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
            ->assertJsonStructure([
                'data' => [
                    TroubleshootCreateMutation::NAME => [
                        'id',
                        'active',
                        'name',
                        'group' => [
                            'id'
                        ],
                        'pdf' => [
                            'file_name'
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    TroubleshootCreateMutation::NAME => [
                        'active' => true,
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
