<?php

namespace App\Http\Controllers\Api\V1\Agreement;

use App\Events\Firebase\FcmPush;
use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\Agreement\AgreementCreateRequest;
use App\Models\Agreement\Agreement as ModelAgreement;
use App\Repositories\Order\AgreementRepository;
use App\Repositories\User\UserRepository;
use App\Services\Firebase\FcmAction;
use App\Services\Order\AgreementService;
use App\ValueObjects\Phone;
use Illuminate\Http\JsonResponse;

class AgreementController extends ApiController
{
    public function __construct(
        protected AgreementService $service,
        protected AgreementRepository $repo,
        protected UserRepository $repoUser,
    )
    {}

    /**
     * @OA\Post (
     *     path="agreements",
     *     tags={"Agreemnet"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Create or update agreement",
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AgreementCreateRequest")),
     *
     *     @OA\Response(response="200", description="OK", @OA\JsonContent(ref="#/components/schemas/SuccessResponse")),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function createOrUpdate(AgreementCreateRequest $request): JsonResponse
    {
        AALogger::info('Запрос на создание согласований', $request->all());
        try {
            $data = $request->all();
            /** @var $model ModelAgreement */
            $model = $this->repo->getOneBy("uuid", $request["id"]);

            if($model){
                makeTransaction(fn() => $this->service->edit($model, $data));

            } else {
                $model = null;
                makeTransaction(function () use($data, &$model){
                    $model = $this->service->create($data);
                });

                if($user = $this->repoUser->getByPhone((new Phone($request['phone'])))){
                    event(new FcmPush(
                        $user,
                        FcmAction::create(FcmAction::RECONCILIATION_WORK, [
                            'class' => FcmAction::MODEL_AGREEMENT,
                            'id' => $model->id
                        ], $model),
                        $model
                    ));
                }
            }

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}

