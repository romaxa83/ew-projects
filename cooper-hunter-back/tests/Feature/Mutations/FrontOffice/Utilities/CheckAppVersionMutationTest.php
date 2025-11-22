<?php

namespace Tests\Feature\Mutations\FrontOffice\Utilities;

use App\Enums\Utils\Versioning\VersionStatusEnum;
use App\GraphQL\Mutations\FrontOffice\Utilities\AppVersionStatusMutation;
use App\Models\Utils\Version;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CheckAppVersionMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = AppVersionStatusMutation::NAME;

    public function test_check(): void
    {
        Version::factory()
            ->create(
                [
                    'recommended_version' => '1.0.1',
                    'required_version' => '1.0.0',
                ]
            );

        $version = '1.0.2';

        $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(compact('version'))
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => VersionStatusEnum::OK
                    ],
                ]
            );
    }
}