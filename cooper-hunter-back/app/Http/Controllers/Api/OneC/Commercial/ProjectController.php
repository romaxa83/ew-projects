<?php

namespace App\Http\Controllers\Api\OneC\Commercial;

use App\Dto\Commercial\CommercialProjectUnitsDto;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\OneC\Commercial\ProjectUnitsRequest;
use App\Models\Commercial\CommercialProject;
use App\Repositories\Commercial\CommercialProjectRepository;
use App\Services\Commercial\CommercialProjectService;
use App\Services\Commercial\CommercialProjectUnitService;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Http\Request;

/**
 * @group Commercial project
 */
class ProjectController extends ApiController
{
    public function __construct(
        protected CommercialProjectRepository $repo,
        protected CommercialProjectService $service,
        protected CommercialProjectUnitService $serviceUnit
    )
    {}

    /**
     * Start commissioning
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function startCommissioning($guid): JsonResponse
    {
        try {
            /** @var $model CommercialProject */
            $model = $this->repo->getByFields(['guid' => $guid],[],true);

            makeTransaction(
                function () use ($model) {
                    $this->service->startPreCommissioning($model);
                }
            );

            return self::responseSuccess("Done");
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Add or update units
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function addUnits(ProjectUnitsRequest $request, $guid): JsonResponse
    {
        logger('CREATE OR UPDATE COMMERCIAL_PROJECT_UNITS', $request->all());

        try {
            /** @var $model CommercialProject */
            $model = $this->repo->getByFields(['guid' => $guid],['units'],true);

            makeTransaction(
                function () use ($model, $request) {
                    $this->serviceUnit->createOrUpdate(
                        $model,
                        CommercialProjectUnitsDto::byArgs($request->all())
                    );
                }
            );

            return self::responseSuccess("Done");
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove a few units by serial-number
     *
     * @bodyParam data object[] required
     * @bodyParam data.0 string Example: 2a48aa4b
     * @bodyParam data.1 string Example: 5a48aa4b
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function removeUnits(Request $request, $guid): JsonResponse
    {
        $data = data_get($request->all(), 'data', []);
        logger('DELETE COMMERCIAL_PROJECT_UNITS', $data);

        try {
            /** @var $model CommercialProject */
            $model = $this->repo->getByFields(['guid' => $guid],['units'],true);

            $count = makeTransaction(
                function () use ($model, $data) {
                    return $this->serviceUnit->removeBySerialNumber($model, $data);
                }
            );

            return self::responseSuccess("Delete - [$count], records");
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }
}
