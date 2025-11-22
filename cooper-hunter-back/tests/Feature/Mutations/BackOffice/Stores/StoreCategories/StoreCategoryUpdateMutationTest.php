<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Stores\StoreCategories;

use App\GraphQL\Mutations\BackOffice\Stores\StoreCategories\StoreCategoryUpdateMutation;
use App\Models\Stores\StoreCategory;
use App\Models\Stores\StoreCategoryTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StoreCategoryUpdateMutationTest extends StoreCategoryCreateMutationTest
{
    use DatabaseTransactions;

    public const MUTATION = StoreCategoryUpdateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $id = StoreCategory::factory()
            ->has(StoreCategoryTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->id;

        $this->getMutation(compact('id'))
            ->assertJsonStructure(
                [
                    'data' => [
                        static::MUTATION => $this->getSelect(),
                    ],
                ]
            );
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $id = StoreCategory::factory()
            ->has(StoreCategoryTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->id;

        $this->assertServerError($this->getMutation(compact('id')), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $id = StoreCategory::factory()
            ->has(StoreCategoryTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->id;

        $this->assertServerError($this->getMutation(compact('id')), 'Unauthorized');
    }
}
