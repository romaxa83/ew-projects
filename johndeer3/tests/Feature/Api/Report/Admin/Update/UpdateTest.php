<?php

namespace Tests\Feature\Api\Report\Admin\Update;

use App\Helpers\DateFormat;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Services\Report\ReportService;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success_main_data()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data = CreateTest::fullData();
        $data['title'] = 'update_title';
        unset($data['location']);

        $rep = $this->reportBuilder->create();

        $this->assertNotEquals($rep->title, $data['title']);
        $this->assertNotEquals($rep->salesman_name, $data['salesman_name']);
        $this->assertNotEquals($rep->result, $data['result']);
        $this->assertNotEquals($rep->assignment, $data['assignment']);
        $this->assertNotEquals($rep->client_comment, $data['client_comment']);
        $this->assertNotEquals($rep->client_email, $data['client_email']);

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep->id,
                'title' => $data['title'],
                'salesman_name' => $data['salesman_name'],
                'result' => $data['result'],
                'assignment' =>  $data['assignment'],
                'client_email' => $data['client_email'],
                'client_comment' => $data['client_comment'],
            ]))
        ;
    }

    /** @test */
    public function success_empty()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $rep = $this->reportBuilder->create();

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), [])
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep->id,
                'title' => $rep->title,
                'salesman_name' => $rep->salesman_name,
                'result' => $rep->result,
                'assignment' => $rep->assignment,
                'client_email' => $rep->client_email,
                'client_comment' => $rep->client_comment,
            ]))
        ;
    }

    /** @test */
    public function success_comment()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data['comment'] = 'some comment';
        /** @var $rep Report */
        $rep = $this->reportBuilder->setComment("comment")->create();

        $this->assertNotEquals($rep->comment->text, $data['comment']);

        $date = Carbon::now();
        CarbonImmutable::setTestNow($date);

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureSuccessResponse([
                'comment' => [
                    "comment" => "some comment",
                    "created" => DateFormat::front($date->toDateTimeString())
                ],
            ]))
        ;
    }

    /** @test */
    public function success_not_have_comment()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data['comment'] = 'some comment';

        $rep = $this->reportBuilder->create();

        $this->assertNull($rep->comment);

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), $data)
            ->assertJson($this->structureSuccessResponse([
                'comment' => null,
            ]))
        ;

        $rep->refresh();
        $this->assertNull($rep->comment);
    }

    /** @test */
    public function success_different_status()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $data["result"] = 'resssult';

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();
        $rep_2 = $this->reportBuilder->setStatus(ReportStatus::OPEN_EDIT)->create();
        $rep_3 = $this->reportBuilder->setStatus(ReportStatus::EDITED)->create();
        $rep_4 = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)->create();
        $rep_5 = $this->reportBuilder->setStatus(ReportStatus::VERIFY)->create();

        $this->postJson(route('api.report.update', ["report" => $rep_1]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep_1->id,
                'status' => ReportStatus::CREATED,
                'result' => $data['result']
            ]))
        ;
        $this->postJson(route('api.report.update', ["report" => $rep_2]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep_2->id,
                'status' => ReportStatus::OPEN_EDIT,
                'result' => $data['result']
            ]))
        ;
        $this->postJson(route('api.report.update', ["report" => $rep_3]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep_3->id,
                'status' => ReportStatus::EDITED,
                'result' => $data['result']
            ]))
        ;
        $this->postJson(route('api.report.update', ["report" => $rep_4]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep_4->id,
                'status' => ReportStatus::IN_PROCESS,
                'result' => $data['result']
            ]))
        ;
        $this->postJson(route('api.report.update', ["report" => $rep_5]), $data)
            ->assertJson($this->structureSuccessResponse([
                'id' => $rep_5->id,
                'status' => ReportStatus::VERIFY,
                'result' => $data['result']
            ]))
        ;
    }

    /**
     * @test
     * @dataProvider validate_main_data
     */
    public function validate_data($field, $value, $msg)
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $rep Report */
        $rep = $this->reportBuilder->setUser($user)->create();

        $this->postJson(route('api.report.update', [
            "report" => $rep
        ]), [
            $field => $value
        ])
            ->assertJson($this->structureErrorResponse([$msg]))
        ;
    }

    public function validate_main_data(): array
    {
        return [
            ['title', 9999, 'The title must be a string.'],
            ['salesman_name', 9999, 'The salesman_name must be a string.'],
            ['assignment', 9999, 'The assignment must be a string.'],
            ['result', 9999, 'The result must be a string.'],
            ['client_comment', 9999, 'The client comment must be a string.'],
            ['client_email', 9999, 'The client email must be a string.'],
            ['comment', 9999, 'The comment must be a string.'],
        ];
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(ReportService::class, function(MockInterface $mock){
            $mock->shouldReceive("update")
                ->andThrows(\Exception::class, "some exception message");
        });

        $rep = $this->reportBuilder->create();

        $this->postJson(route('api.report.update', ["report" => $rep]), [])
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data["result"] = 'resssult';

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $this->postJson(route('api.report.update', ["report" => $rep_1]), $data)
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = $this->userBuilder->create();

        $data["result"] = 'resssult';

        $rep_1 = $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $this->postJson(route('api.report.update', ["report" => $rep_1]), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
