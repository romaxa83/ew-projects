<?php

namespace Tests\Feature\Api\Report\Admin;

use App\Models\Comment;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Services\CommentService;
use App\Type\ReportStatus;
use Illuminate\Http\Response;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommentTest extends TestCase
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
    public function success()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $comment = "some comment";

        /** @var $report Report */
        $report = $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $this->assertNull($report->comment);
        $this->assertFalse($report->isOpenEdit());

        $this->postJson(route('admin.comment.create', [
            "report" => $report
        ]), ["comment" => $comment])
            ->assertJson($this->structureSuccessResponse(__('message.comment_success')))
        ;

        $report->refresh();

        $this->assertEquals($report->comment->text, $comment);
        $this->assertEquals($report->comment->author_id, $user->id);
        $this->assertEquals($report->comment->model, Comment::COMMENT_BY_REPORT);
        $this->assertTrue($report->isOpenEdit());

        $commentModel = Comment::query()->where('text', $comment)->first();

        $this->assertTrue($commentModel->entity instanceof Report);
        $this->assertEquals($commentModel->entity->id, $report->id);
    }

    /** @test */
    public function success_update()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $comment = "some comment";
        $commentNew = "new some comment";

        /** @var $report Report */
        $report = $this->reportBuilder->setComment($comment)
            ->setStatus(ReportStatus::CREATED)->create();

        $this->assertNotEquals($report->comment->text, $commentNew);

        $this->postJson(route('admin.comment.create', [
            "report" => $report
        ]), ["comment" => $commentNew])
            ->assertJson($this->structureSuccessResponse(__('message.comment_success')))
        ;

        $report->refresh();

        $this->assertEquals($report->comment->text, $commentNew);
        $this->assertEquals($report->comment->author_id, $user->id);
        $this->assertEquals($report->comment->model, Comment::COMMENT_BY_REPORT);
        $this->assertTrue($report->isOpenEdit());
    }

    /** @test */
    public function fail_verify_report()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        $comment = "some comment";

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setStatus(ReportStatus::VERIFY)->create();

        $this->assertTrue($report->isVerify());

        $this->postJson(route('admin.comment.create', [
            "report" => $report
        ]), ["comment" => $comment])
            ->assertJson($this->structureErrorResponse(__('message.report_verify_not_comment')))
        ;

        $report->refresh();

        $this->assertNull($report->comment);
    }

    /** @test */
    public function fail_without_comment()
    {
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        /** @var $report Report */
        $report = $this->reportBuilder
            ->setStatus(ReportStatus::VERIFY)->create();

        $this->postJson(route('admin.comment.create', [
            "report" => $report
        ]), [])
            ->assertJson($this->structureErrorResponse(["The comment field is required."]));
    }

    /** @test */
    public function fail_return_some_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $this->mock(CommentService::class, function(MockInterface $mock){
            $mock->shouldReceive("createOrUpdateByReport")
                ->andThrows(\Exception::class, "some exception message");
        });

        $comment = "some comment";

        /** @var $report Report */
        $report = $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $this->postJson(route('admin.comment.create', [
            "report" => $report
        ]), ["comment" => $comment])
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $comment = "some comment";

        /** @var $report Report */
        $report = $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $this->postJson(route('admin.comment.create', [
            "report" => $report
        ]), ["comment" => $comment])
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->userBuilder->create();

        $comment = "some comment";

        /** @var $report Report */
        $report = $this->reportBuilder->setStatus(ReportStatus::CREATED)->create();

        $this->postJson(route('admin.comment.create', [
            "report" => $report
        ]), ["comment" => $comment])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}

