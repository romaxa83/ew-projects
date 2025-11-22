<?php

namespace Tests\Feature\Mutations\BackOffice\Utilities;

use App\GraphQL\Mutations\BackOffice\Utilities\CreateOrUpdateAppVersionsMutation;
use App\Models\Utils\Version;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CreateOrUpdateAppVersionsMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CreateOrUpdateAppVersionsMutation::NAME;

    public function test_rule(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertServerError(
            $this->postGraphQLBackOffice(
                $this->getQuery('1.0.3', '1.0.4')
            ),
            'validation'
        );
    }

    public function getQuery(string $recommended, string $required): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'recommended_version' => $recommended,
                    'required_version' => $required,
                ]
            )
            ->select(
                [
                    'recommended_version',
                    'required_version'
                ]
            )
            ->make();
    }

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice(
            $this->getQuery($recommended = '1.0.3', $required = '1.0.0')
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'recommended_version' => $recommended,
                            'required_version' => $required,
                        ]
                    ]
                ]
            );
    }

    public function test_update(): void
    {
        $this->loginAsSuperAdmin();

        $version = Version::factory()
            ->create(
                [
                    'recommended_version' => '1.0.1',
                    'required_version' => '1.0.0',
                ]
            );

        self::assertSame('1.0.1', $version->recommended_version);
        self::assertSame('1.0.0', $version->required_version);

        $this->postGraphQLBackOffice(
            $this->getQuery($recommended = '7.7.7', $required = '1.0.0')
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'recommended_version' => $recommended,
                            'required_version' => $required,
                        ]
                    ]
                ]
            );

        $version->refresh();

        self::assertSame($recommended, $version->recommended_version);
        self::assertSame($required, $version->required_version);
    }

    public function test_update_equal_versions(): void
    {
        $this->loginAsSuperAdmin();

        $version = Version::factory()
            ->create(
                [
                    'recommended_version' => '1.0.1',
                    'required_version' => '1.0.0',
                ]
            );

        self::assertSame('1.0.1', $version->recommended_version);
        self::assertSame('1.0.0', $version->required_version);

        $this->postGraphQLBackOffice(
            $this->getQuery($recommended = '7.7.7', $required = '7.7.7')
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'recommended_version' => $recommended,
                            'required_version' => $required,
                        ]
                    ]
                ]
            );

        $version->refresh();

        self::assertSame($recommended, $version->recommended_version);
        self::assertSame($required, $version->required_version);
    }
}