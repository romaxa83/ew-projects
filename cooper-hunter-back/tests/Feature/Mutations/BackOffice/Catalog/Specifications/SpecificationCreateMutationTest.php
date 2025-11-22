<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Specifications;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications\SpecificationCreateMutation;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Features\SpecificationTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SpecificationCreateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SpecificationCreateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertDatabaseCount(Specification::TABLE, 0);
        $this->assertDatabaseCount(SpecificationTranslation::TABLE, 0);

        $translationEn = [
            'language' => 'en',
            'title' => 'en title',
            'description' => 'en description',
            'seo_title' => 'custom seo title en',
            'seo_description' => 'custom seo description en',
            'seo_h1' => 'custom seo h1 en',
        ];

        $translationEs = [
            'language' => 'es',
            'title' => 'es title',
            'description' => 'es description',
            'seo_title' => 'custom seo title es',
            'seo_description' => 'custom seo description es',
            'seo_h1' => 'custom seo h1 es',
        ];

        $payload = $this->getData();
        $payload['specification']['translations'] = [$translationEn, $translationEs];

        $this->assertDatabaseMissing(SpecificationTranslation::TABLE, $translationEn);
        $this->assertDatabaseMissing(SpecificationTranslation::TABLE, $translationEs);

        $this->mutation($payload)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id'
                        ],
                    ]
                ]
            );

        $this->assertDatabaseCount(Specification::TABLE, 1);
        $this->assertDatabaseCount(SpecificationTranslation::TABLE, 2);

        $this->assertDatabaseHas(SpecificationTranslation::TABLE, $translationEn);
        $this->assertDatabaseHas(SpecificationTranslation::TABLE, $translationEs);
    }

    protected function mutation(array $args): TestResponse
    {
        $query = new GraphQLQuery(
            static::MUTATION,
            $args,
            [
                'id',
                'icon',
                'translation' => [
                    'language',
                    'title',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ],
            ]
        );

        return $this->postGraphQLBackOffice($query->getMutation());
    }

    protected function getData(): array
    {
        return [
            'specification' => [
                'active' => true,
                'icon' => 'icon_name',
                'translations' => [
                    [
                        'language' => 'en',
                        'title' => 'en title',
                        'description' => 'en description',
                    ],
                    [
                        'language' => 'es',
                        'title' => 'es title',
                        'description' => 'es description',
                    ],
                ],
            ]
        ];
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $this->assertServerError($this->mutation($this->getData()), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $this->assertServerError($this->mutation($this->getData()), 'Unauthorized');
    }
}
