<?php

namespace App\Http\Controllers\Api\QuestionAnswer;

use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Models\Language;
use App\Models\QuestionAnswer\QuestionAnswer;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\QuestionAnswer\QuestionAnswerResource;
use App\Http\Requests\QuestionAnswer\QuestionAnswerRequest;
use App\Http\Resources\QuestionAnswer\QuestionAnswerPaginatedResource;
use App\Http\Resources\QuestionAnswer\QuestionAnswerFullResource;
use Illuminate\Http\Response;
use Throwable;
use Config;

class QuestionAnswerController extends ApiController
{
    private $lang;

    public function __construct()
    {
        $this->lang = Config::get('app.locale') ? Config::get('app.locale') : 'en';
        $hasLanguage = Language::whereSlug($this->lang)->count();
        if (!$hasLanguage) {
            $this->lang = 'en';
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return QuestionAnswerPaginatedResource
     * @throws AuthorizationException
     *     @OA\Get(
     *     path="/api/question-answer",
     *     tags={"QuestionAnswer"},
     *     summary="Get QuestionAnswer paginated list",
     *     operationId="Get QuestionAnswer data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="serach",
     *          in="query",
     *          description="Serach for question or answer",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="question name"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="5"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="QuestionAnswer per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field to sort by",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="question",
     *              enum ={"answer","question"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Sort order",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum ={"asc","desc"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/QuestionAnswerPaginatedResource")
     *     ),
     * )
     *
     */
    public function index(Request $request) : QuestionAnswerPaginatedResource
    {
        $this->authorize('viewList', QuestionAnswer::class);

        $orderBy = in_array($request->input('order_by'), ['question', 'answer'])
            ? $request->input('order_by') : 'question_' . $this->lang;
        $orderByType = in_array($request->input('order_type'), ['asc', 'desc'])
            ? $request->input('order_type') : 'asc';
        $perPage = (int) $request->input('per_page', 10);

        $questionAnswers = QuestionAnswer::filter($request->only(['search']))
            ->orderBy($orderBy, $orderByType)
            ->paginate($perPage);

        return  (new QuestionAnswerPaginatedResource($questionAnswers))
            ->withQuery($request->query());
    }

    /**
     * Display the specified resource.
     *
     * @param QuestionAnswer $questionAnswer
     * @return QuestionAnswerFullResource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/question-answer/{questionAnswerId}/full",
     *     tags={"QuestionAnswer"},
     *     summary="Get Question-Answer record full",
     *     operationId="Get Question-Answer record full",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="QuestionAnswer id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/QuestionAnswerFullResource")
     *     ),
     * )
     */
    public function showFull(QuestionAnswer $questionAnswer)
    {
        $this->authorize('update', $questionAnswer);

        return QuestionAnswerFullResource::make($questionAnswer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QuestionAnswerRequest $request
     * @return QuestionAnswerResource
     * @throws Throwable
     * @OA\Post(
     *     path="/api/question-answer",
     *     tags={"QuestionAnswer"},
     *     summary="Create Question and Answer",
     *     operationId="Create Question and Answer",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="question_en",
     *          in="query",
     *          description="Question in English",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="How can order delivery?",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="answer_en",
     *          in="query",
     *          description="Answer in English",
     *          required=true,
     *          @OA\Schema(
     *              type="text",
     *              default="Dear Sir or Madam, you can order delivery using our system.",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="question_es",
     *          in="query",
     *          description="Question in Spanish",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="¿Cómo puedo ordenar la entrega?",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="answer_es",
     *          in="query",
     *          description="Answer in Spanish",
     *          required=true,
     *          @OA\Schema(
     *              type="text",
     *              default="Estimado señor o señora, puede solicitar la entrega utilizando nuestro sistema.",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="question_ru",
     *          in="query",
     *          description="Question in Russian",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Как можно заказать доставку?",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="answer_ru",
     *          in="query",
     *          description="Answer in Russian",
     *          required=true,
     *          @OA\Schema(
     *              type="text",
     *              default="Уважаемые дамы и господа, вы можете заказать доставку с помощью нашей системы.",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/QuestionAnswerFullResource")
     *     ),
     * )
     */
    public function store(QuestionAnswerRequest $request)
    {
        $this->authorize('create', QuestionAnswer::class);

        $questionAnswer = new QuestionAnswer();
        $questionAnswer->fill($request->validated())
            ->saveOrFail();

        event(new ModelChanged($questionAnswer, 'history.faq_created', [
            'full_name' => $request->user()->full_name,
            'email' => $request->user()->email,
            'question_en' => $questionAnswer->question_en,
        ]));

        return QuestionAnswerFullResource::make($questionAnswer);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param QuestionAnswerRequest $request
     * @param QuestionAnswer $questionAnswer
     * @return QuestionAnswerResource|JsonResponse
     * @throws AuthorizationException
     *     @OA\Put(
     *     path="/api/question-answer/{questionAnswerId}",
     *     tags={"QuestionAnswer"},
     *     summary="Update QuestionAnswer",
     *     operationId="Update QuestionAnswer",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="answer_en",
     *          in="query",
     *          description="Answer in English",
     *          required=true,
     *          @OA\Schema(
     *              type="text",
     *              default="Dear Sir or Madam, you can order delivery using our system.",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="question_es",
     *          in="query",
     *          description="Question in Spanish",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="¿Cómo puedo ordenar la entrega?",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="answer_es",
     *          in="query",
     *          description="Answer in Spanish",
     *          required=true,
     *          @OA\Schema(
     *              type="text",
     *              default="Estimado señor o señora, puede solicitar la entrega utilizando nuestro sistema.",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="question_ru",
     *          in="query",
     *          description="Question in Russian",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Как можно заказать доставку?",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="answer_ru",
     *          in="query",
     *          description="Answer in Russian",
     *          required=true,
     *          @OA\Schema(
     *              type="text",
     *              default="Уважаемые дамы и господа, вы можете заказать доставку с помощью нашей системы.",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/QuestionAnswerFullResource")
     *     ),
     * )
     */
    public function update(QuestionAnswerRequest $request, QuestionAnswer $questionAnswer)
    {
        $this->authorize('update', $questionAnswer);

        $questionAnswer->fill($request->validated())
            ->save();

        event(new ModelChanged($questionAnswer, 'history.faq_updated', [
            'full_name' => $request->user()->full_name,
            'email' => $request->user()->email,
            'question_en' => $questionAnswer->question_en,
        ]));

        return new QuestionAnswerFullResource($questionAnswer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param QuestionAnswer $questionAnswer
     * @return JsonResponse
     * @throws Exception
     *     @OA\Delete(
     *     path="/api/question-answer/{questionAnswerId}",
     *     tags={"QuestionAnswer"},
     *     summary="Delete Question and Answer",
     *     operationId="Delete Question and Answer",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function destroy(QuestionAnswer $questionAnswer)
    {
        $this->authorize('delete', $questionAnswer);

        $questionAnswer->delete();

        event(new ModelChanged($questionAnswer, 'history.faq_deleted', [
            'full_name' => request()->user()->full_name,
            'email' => request()->user()->email,
            'question_en' => $questionAnswer->question_en,
        ]));

        return $this->makeSuccessResponse(
            'The item has been deleted successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
