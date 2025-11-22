<?php

namespace App\Http\Controllers\Api\OneC\Commercial;

use App\Dto\Commercial\TaxesDto;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\OneC\Commercial\TaxRequest;
use App\Services\Commercial\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * @group Tax
 */
class TaxController extends ApiController
{
    /**
     * Create or update
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function createOrUpdate(TaxRequest $request, TaxService $service): JsonResponse
    {
        $data = $request->all();

        logger('CREATE OR UPDATE TAXES', $data);

        try {
            makeTransaction(
                function () use ($service, $data) {
                    $service->createOrUpdate(
                        TaxesDto::byArgs($data)
                    );
                }
            );

            return self::responseSuccess("Done");
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove a few by guid
     *
     * @bodyParam data object[] required
     * @bodyParam data.0 string Example: 2a48aa4b-52ff-444f-8435-e7e761eb3e62
     * @bodyParam data.1 string Example: 5a48aa4b-52ff-444f-8435-e7e761eb3e63
     *
     * @responseFile 200 docs/api/success.json
     * @responseFile 500 docs/api/error.json
     *
     * @throws Throwable
     */
    public function remove(Request $request, TaxService $service): JsonResponse
    {
        $data = data_get($request->all(), 'data');

        logger('DELETE TAXES', $data);

        try {
            $count = makeTransaction(
                function () use ($service, $data) {
                    return $service->removeByGuids($data);
                }
            );

            return self::responseSuccess("Delete - [$count], records");
        } catch (\Throwable $e) {
            return self::responseError($e->getMessage(), $e->getCode());
        }
    }
}

