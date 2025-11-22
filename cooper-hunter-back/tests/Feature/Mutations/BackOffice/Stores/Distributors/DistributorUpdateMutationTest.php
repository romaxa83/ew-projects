<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\Stores\Distributors;

use App\GraphQL\Mutations\BackOffice\Stores\Distributors\DistributorUpdateMutation;
use App\Models\Stores\Distributor;
use App\Models\Stores\DistributorTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DistributorUpdateMutationTest extends DistributorCreateMutationTest
{
    use DatabaseTransactions;

    public const MUTATION = DistributorUpdateMutation::NAME;

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
        return Distributor::factory()
            ->has(DistributorTranslation::factory()->allLocales(), 'translations')
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
