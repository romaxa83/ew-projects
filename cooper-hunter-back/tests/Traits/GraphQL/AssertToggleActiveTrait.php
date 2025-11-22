<?php

namespace Tests\Traits\GraphQL;

use App\Models\BaseModel;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;

trait AssertToggleActiveTrait
{
    protected function assertToggleActive(BaseModel $model, bool $dump = false): void
    {
        $query = GraphQLQuery::mutation(static::MUTATION)
            ->args(
                [
                    'id' => $model->id
                ],
            )
            ->select(
                [
                    'id',
                    'active',
                ]
            )->make();


        $response = $this->postGraphQLBackOffice($query);

        if ($dump) {
            $response->dump();
        }

        $response->assertOk()
            ->assertJsonPath('data.' . self::MUTATION . '.active', false)
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id',
                            'active',
                        ],
                    ],
                ]
            );
    }
}
