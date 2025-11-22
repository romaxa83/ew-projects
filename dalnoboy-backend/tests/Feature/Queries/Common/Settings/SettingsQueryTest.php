<?php

namespace Tests\Feature\Queries\Common\Settings;

use App\GraphQL\Queries\Common\Settings\BaseSettingsQuery;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SettingsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_tires_list_by_admin(): void
    {
        $this->loginAsAdminWithRole();

        $this->check();
    }

    private function check(bool $backoffice = true): void
    {
        $this->{'postGraphQl' . ($backoffice ? 'BackOffice' : '')}(
            GraphQLQuery::query(BaseSettingsQuery::NAME)
                ->select(
                    [
                        'phone',
                        'email'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        BaseSettingsQuery::NAME => [
                            'email',
                            'phone',
                        ],
                    ],
                ]
            );
    }

    public function test_get_tires_list_by_user(): void
    {
        $this->loginAsUserWithRole();

        $this->check(false);
    }
}
