<?php

namespace Tests\Feature\Queries\BackOffice\Musics;

use App\GraphQL\Queries\BackOffice;
use App\Models\Musics\Music;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Musics\MusicBuilder;
use Tests\TestCase;

class MusicListQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackOffice\Musics\MusicsListQuery::NAME;

    protected DepartmentBuilder $departmentBuilder;
    protected MusicBuilder $musicBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->musicBuilder = resolve(MusicBuilder::class);
    }

    /** @test */
    public function success_list(): void
    {
        $this->loginAsSuperAdmin();

        $m_1 = $this->musicBuilder->active()->create();
        $m_2 = $this->musicBuilder->active()->create();
        $m_3 = $this->musicBuilder->active(false)->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $m_3->id],
                        ['id' => $m_2->id],
                        ['id' => $m_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::QUERY)
        ;
    }

    /** @test */
    public function success_empty(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => []
                ]
            ])
            ->assertJsonCount(0, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStr(): string
    {
        return sprintf(
            '
            {
                %s {
                    id
                }
            }',
            self::QUERY
        );
    }

    /** @test */
    public function filter_by_id(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Music */
        $model = $this->musicBuilder->create();
        $this->musicBuilder->create();
        $this->musicBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrById($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        [
                            'id' => $model->id,
                            'record' => null
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrById($id): string
    {
        return sprintf(
            '
            {
                %s (id: %s){
                    id
                    record {
                        id
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function filter_by_active(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $d_1 Music */
        $d_1 = $this->musicBuilder->active()->create();
        $d_2 = $this->musicBuilder->active(false)->create();
        $d_3 = $this->musicBuilder->active()->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrByActive('true')
        ])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        ['id' => $d_3->id],
                        ['id' => $d_1->id],
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.'.self::QUERY)
        ;
    }

    protected function getQueryStrByActive($value): string
    {
        return sprintf(
            '
            {
                %s (active: %s){
                    id
                }
            }',
            self::QUERY,
            $value
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_auth(): void
    {
        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr()
        ])
        ;

        $this->assertUnauthorized($res);
    }
}

