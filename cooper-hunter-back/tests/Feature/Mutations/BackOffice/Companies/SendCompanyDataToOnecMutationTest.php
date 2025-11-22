<?php

namespace Tests\Feature\Mutations\BackOffice\Companies;

use App\GraphQL\Mutations\BackOffice\Companies;
use App\Models\Companies\Company;
use App\Services\OneC\Client\RequestClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\Builders\Company\CompanyBuilder;
use Tests\TestCase;

class SendCompanyDataToOnecMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = Companies\SendCompanyDataToOnecMutation::NAME;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    /** @test */
    public function success_send(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()->create();

        $res = [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->assertNull($company->guid);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($company->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' =>__('messages.company.send_data_to_onec.message'),
                        'type' => 'success',
                    ],
                ]
            ])
        ;

        $company->refresh();

        $this->assertEquals($company->guid, data_get($res, 'guid'));
    }

    /** @test */
    public function fail_company_has_guid(): void
    {
        $this->loginAsSuperAdmin();

        $guid = '80751f6f-9b0d-3c5a-99fc-307d043641d5';
        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()
            ->setData(['guid' => $guid])->create();

        $res = [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];

        $this->mock(RequestClient::class, function(MockInterface $mock) use($res) {
            $mock->shouldReceive("postRequest")
                ->andReturn($res);
        });

        $this->assertEquals($company->guid, $guid);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($company->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' =>__('messages.company.send_data_to_onec.has guid'),
                        'type' => 'warning',
                    ],
                ]
            ])
        ;

        $this->assertEquals($company->guid, $guid);
    }

    /** @test */
    public function something_wrong_into_service(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $company Company */
        $company = $this->companyBuilder->withContacts()->create();

        $this->mock(RequestClient::class, function(MockInterface $mock) {
            $mock->shouldReceive("postRequest")
                ->andThrows(\Exception::class, "some exception message")
            ;
        });

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($company->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'message' => "some exception message",
                        'type' => 'warning',
                    ],
                ]
            ])
        ;
    }

    protected function getQueryStr($id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                ) {
                    type
                    message
                }
            }',
            self::MUTATION,
            $id
        );
    }
}

