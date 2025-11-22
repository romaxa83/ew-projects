<?php

namespace Tests\Feature\Queries\Common\Warranty\WarrantyInfo;

use App\GraphQL\Queries\BackOffice\Warranty\WarrantyInfo\WarrantyInfoQuery;
use App\Models\Warranty\WarrantyInfo\WarrantyInfo;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackage;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoPackageTranslation;
use App\Models\Warranty\WarrantyInfo\WarrantyInfoTranslation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WarrantyInfoQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_warranty_info(): void
    {
        $this->assertCanViewWarrantyInfo(WarrantyInfoQuery::NAME);
    }

    protected function assertCanViewWarrantyInfo(string $queryName, bool $forUser = false): void
    {
        WarrantyInfo::factory()
            ->has(
                WarrantyInfoTranslation::factory()
                    ->allLocales(),
                'translations'
            )
            ->has(
                WarrantyInfoPackage::factory()
                    ->has(
                        WarrantyInfoPackageTranslation::factory()
                            ->allLocales(),
                        'translations'
                    ),
                'packages'
            )
            ->create();

        $query = new GraphQLQuery(
            $queryName,
            select: [
                'id',
                'video_link',
                'pdf' => [
                    'url',
                ],
                'translation' => [
                    'description',
                    'notice',
                ],
                'packages' => [
                    'sort',
                    'translation' => [
                        'title',
                        'description',
                    ],
                    'image' => [
                        'url'
                    ],
                ],
            ]
        );

        $response = $forUser
            ? $this->postGraphQL($query->getQuery())
            : $this->postGraphQLBackOffice($query->getQuery());

        $response->assertJsonStructure(
            [
                'data' => [
                    $queryName => [
                        'id',
                        'video_link',
                        'pdf' => [
                            'url',
                        ],
                        'translation' => [
                            'description',
                            'notice',
                        ],
                        'packages' => [
                            [
                                'sort',
                                'translation' => [
                                    'title',
                                    'description',
                                ],
                                'image' => [
                                    'url'
                                ],
                            ]
                        ],
                    ],
                ],
            ]
        );
    }

    public function test_user_can_view_warranty_info(): void
    {
        $this->assertCanViewWarrantyInfo(WarrantyInfoQuery::NAME, true);
    }
}
