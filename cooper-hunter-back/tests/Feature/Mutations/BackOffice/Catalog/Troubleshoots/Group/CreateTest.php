<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupCreateMutation;
use App\Models\Localization\Language;
use App\Permissions\Catalog\Troubleshoots\Group;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Group\CreatePermission::KEY]);
    }

    public function test_success(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TroubleshootGroupCreateMutation::NAME)
            ->args(
                [
                    'active' => true,
                    'translations' => languages()
                        ->map(
                            fn (Language $language) => [
                                'language' => new EnumValue($language->slug),
                                'title' => $this->faker->title,
                                'description' => $this->faker->text
                            ]
                        )
                        ->values()
                        ->toArray()
                ]
            )
            ->select([
                'id',
                'created_at',
                'updated_at',
                'active',
                'translation' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'language',
                ],
                'translations' => [
                    'id',
                    'title',
                    'slug',
                    'description',
                    'language',
                ],
            ])
            ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    TroubleshootGroupCreateMutation::NAME => [
                        'id',
                        'created_at',
                        'updated_at',
                        'active',
                        'translation' => [
                            'id',
                            'title',
                            'slug',
                            'description',
                            'language'
                        ],
                        'translations' => [
                            '*' => [
                                'id',
                                'title',
                                'slug',
                                'description',
                                'language'
                            ]
                        ],
                    ]
                ]
            ]);
    }
}
