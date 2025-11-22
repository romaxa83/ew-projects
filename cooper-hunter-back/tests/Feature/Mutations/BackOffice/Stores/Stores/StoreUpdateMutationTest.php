<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Stores\Stores;

use App\GraphQL\Mutations\BackOffice\Stores\Stores\StoreUpdateMutation;
use App\Models\Stores\Store;
use App\Models\Stores\StoreTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StoreUpdateMutationTest extends StoreCreateMutationTest
{
    use DatabaseTransactions;

    public const MUTATION = StoreUpdateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $id = $this->getId();

        $this->getMutation(compact('id'))
            ->assertJsonStructure(
                [
                    'data' => [
                        static::MUTATION => $this->getSelect(),
                    ],
                ]
            );
    }

    protected function getId(): mixed
    {
        return Store::factory()
            ->has(StoreTranslation::factory()->allLocales(), 'translations')
            ->create()
            ->id;
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $id = $this->getId();

        $this->assertServerError($this->getMutation(compact('id')), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $id = $this->getId();

        $this->assertServerError($this->getMutation(compact('id')), 'Unauthorized');
    }
}
