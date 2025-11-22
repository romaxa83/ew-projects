<?php

namespace Tests\Feature\Mutations\BackOffice\About;

use App\Enums\About\ForMemberPageEnum;
use App\GraphQL\Mutations\BackOffice\About\ForMemberPageMutation;
use App\Models\About\ForMemberPage;
use App\Models\About\ForMemberPageTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ForMemberPageMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = ForMemberPageMutation::NAME;

    public function test_update_rebates_page(): void
    {
        $this->assertCanUpdateForMemberType(ForMemberPageEnum::REBATES());
    }

    public function test_update_for_technician_page(): void
    {
        $this->assertCanUpdateForMemberType(ForMemberPageEnum::FOR_TECHNICIAN());
    }

    protected function assertCanUpdateForMemberType(ForMemberPageEnum $type): void
    {
        ForMemberPage::factory()
            ->has(ForMemberPageTranslation::factory()->allLocales(), 'translations')
            ->type($type)
            ->create();

        $this->assertCanCreateForMemberType($type);

        $this->assertDatabaseCount(ForMemberPage::TABLE, 1);
    }

    protected function assertCanCreateForMemberType(ForMemberPageEnum $for): void
    {
        $this->loginAsSuperAdmin();

        $query = $this->getQuery($for);

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function getQuery(ForMemberPageEnum $for): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'input' => [
                        'for_member_type' => $for,
                        'translations' => [
                            [
                                'language' => 'en',
                                'title' => 'en title',
                                'description' => 'en description',
                                'seo_title' => 'seo_title',
                                'seo_description' => 'seo_description',
                                'seo_h1' => 'seo_h1',
                            ],
                            [
                                'language' => 'es',
                                'title' => 'es title',
                                'description' => 'es description',
                                'seo_title' => 'seo_title2',
                                'seo_description' => 'seo_description2',
                                'seo_h1' => 'seo_h12',
                            ]
                        ],
                    ],
                ]
            )
            ->select(
                [
                    'id',
                    'for_member_type',
                    'translation' => $translation = [
                        'id',
                        'title',
                        'description',
                        'language',
                        'seo_title',
                        'seo_description',
                        'seo_h1',
                    ],
                    'translations' => $translation
                ]
            )
            ->make();
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                self::MUTATION => [
                    'id',
                    'for_member_type',
                    'translation' => [
                        'id',
                        'title',
                        'description',
                        'language',
                        'seo_title',
                        'seo_description',
                        'seo_h1',
                    ],
                    'translations' => [
                        [
                            'id',
                            'title',
                            'description',
                            'language',
                            'seo_title',
                            'seo_description',
                            'seo_h1',
                        ]
                    ],
                ]
            ],
        ];
    }

    public function test_update_for_homeowner_page(): void
    {
        $this->assertCanUpdateForMemberType(ForMemberPageEnum::FOR_HOMEOWNER());
    }

    public function test_create_for_technician_page(): void
    {
        $this->assertCanCreateForMemberType(ForMemberPageEnum::FOR_TECHNICIAN());
    }

    public function test_create_for_homeowner_page(): void
    {
        $this->assertCanCreateForMemberType(ForMemberPageEnum::FOR_HOMEOWNER());
    }
}
