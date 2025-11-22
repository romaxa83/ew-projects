<?php

namespace Tests\Feature\Queries\BackOffice\Technicians;

use App\GraphQL\Queries\BackOffice\Technicians\TechniciansArchiveQuery;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;
use App\Permissions\Technicians;

class TechniciansArchiveQueryTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public const QUERY = TechniciansArchiveQuery::NAME;

    public function test_get_technicians_empty_list(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianArchiveListPermission::KEY]);

        Technician::factory()->count(4)->create();

        $query = $this->getQueryStr();

        $res = $this->postGraphQLBackOffice(compact('query'));

        $this->assertEmpty($res->json('data.techniciansArchive.data'));

        Technician::factory()->count(5)->deleted()->create();

        $res = $this->postGraphQLBackOffice(compact('query'));

        $this->assertNotEmpty($res->json('data.techniciansArchive.data'));
        $this->assertCount(5, $res->json('data.techniciansArchive.data'));
    }

    public function test_not_perm(): void
    {
        $this->loginByAdminManager([Technicians\TechnicianListPermission::KEY]);

        Technician::factory()->count(5)->deleted()->create();

        $query = $this->getQueryStr();

        $res = $this->postGraphQLBackOffice(compact('query'));

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    public function getQueryStr(): string
    {
        return sprintf(
            'query {
                %s {
                    data {
                        first_name
                        is_certified
                        hvac_license
                        epa_license
                        is_verify_email
                        state {
                            name
                            short_name
                        }
                        email
                        lang
                    }
                }
            }',
            self::QUERY,
        );
    }
}
