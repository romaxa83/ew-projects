<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Catalog\Specifications;

use App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications\SpecificationUpdateMutation;
use App\Models\Catalog\Features\Specification;
use App\Models\Catalog\Features\SpecificationTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SpecificationUpdateMutationTest extends SpecificationCreateMutationTest
{
    use DatabaseTransactions;

    public const MUTATION = SpecificationUpdateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $s = Specification::factory()
            ->has(SpecificationTranslation::factory()->allLocales(), 'translations')
            ->create();

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

        $data = $this->getData();
        $data['specification']['id'] = $s->id;
        $data['specification']['translations'] = [$translationEn, $translationEs];

        $this->assertDatabaseMissing(SpecificationTranslation::TABLE, $translationEn);
        $this->assertDatabaseMissing(SpecificationTranslation::TABLE, $translationEs);

        $this->mutation($data)
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

        $this->assertDatabaseHas(SpecificationTranslation::TABLE, $translationEn);
        $this->assertDatabaseHas(SpecificationTranslation::TABLE, $translationEs);
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $s = Specification::factory()
            ->has(SpecificationTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getData();
        $data['specification']['id'] = $s->id;

        $this->assertServerError($this->mutation($data), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $s = Specification::factory()
            ->has(SpecificationTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getData();
        $data['specification']['id'] = $s->id;

        $this->assertServerError($this->mutation($data), 'Unauthorized');
    }
}
