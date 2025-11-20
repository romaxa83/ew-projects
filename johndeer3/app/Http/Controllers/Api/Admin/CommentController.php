<?php

namespace App\Http\Controllers\Api\Admin;

use App\DTO\Comment\CommentDTO;
use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Comment\CommentRequest;
use App\Models\Report\Report;
use App\Services\CommentService;
use App\Services\Report\ReportService;
use App\Type\ReportStatus;

class CommentController extends ApiController
{
    public function __construct(
        protected CommentService $commentService,
        protected ReportService $reportService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/api/admin/{report}/comment",
     *     tags={"Аdmin-panel"},
     *     summary="Оставить комментарий к отчету",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="{report}", in="path", required=true,
     *          description="ID отчета",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *           @OA\JsonContent(ref="#/components/schemas/CommentRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function create(CommentRequest $request, Report $report)
    {
        if($report->isVerify()){
            throw new \Exception(__('message.report_verify_not_comment'));
        }

        $user = \Auth::user();
        try {
            $dto = CommentDTO::byArgs($request->all());
            $dto->authorID = $user->id;

            $this->commentService->createOrUpdateByReport($report, $dto);
            $this->reportService->changeStatus($report, ReportStatus::OPEN_EDIT);

            return $this->successJsonMessage(__('message.comment_success'));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
