<?php

namespace App\Http\Controllers\Api\OneC\Products;

use App\Dto\Catalog\SerialNumberDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Products\SerialNumbersDeleteRequest;
use App\Http\Requests\Api\OneC\Products\SerialNumbersImportRequest;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Services\Catalog\SerialNumberService;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * @group SerialNumbers
 */
class SerialNumbersController extends Controller
{
    /**
     * Destroy
     *
     * @permission catalog.product.delete
     *
     * @response {
     * "success": true,
     * "data": [
     *  'given_serial_numbers' => 200,
     *  'deleted_serial_numbers' => 200,
     *  'errors' => []
     * ],
     * "message": "Serial number deleted"
     * }
     *
     * @throws Throwable
     */
    public function delete(SerialNumbersDeleteRequest $request, SerialNumberService $service): JsonResponse
    {
        $dto = SerialNumberDto::byArgs($request->validated());

        $product = Product::query()
            ->where('guid', $dto->getProductGuid())
            ->firstOrFail();

        $result = $service->delete($product, $dto);

        return $this->success(
            [
                'given_serial_numbers' => count($dto->getSerialNumbers()),
                'deleted_serial_numbers' => $result,
                'errors' => ProductSerialNumber::query()
                    ->whereIn('serial_number', $dto->getSerialNumbers())
                    ->pluck('serial_number')
                    ->toArray()
            ]
        );
    }

    /**
     * Import
     *
     * <aside>
     *  Response description:<br>
     *  <strong>given_serial_numbers</strong> - The total number of serial numbers specified in the request.<br>
     *  <br>
     *  <strong>import_statistics:</strong><br>
     *  <strong>total</strong> - The total number of serial numbers present in the specified product since the current moment.<br>
     *  <strong>new</strong> - Total number of serial numbers added.<br>
     *  <strong>exists</strong> - The total number of already existing serial numbers for the specified product.<br>
     *  <strong>updated</strong> - Total number of serial numbers migrated from other products.<br>
     * </aside>
     *
     * @permission catalog.product.create
     *
     * @response {
     * "success": true,
     * "data": [
     *  "given_serial_numbers" => 3,
     *  "import_statistics" => {
     *      "total": 3,
     *      "new": 1,
     *      "exists": 1,
     *      "updated": 1
     *  },
     *  "errors" => []
     * ],
     * "message": "Success"
     * }
     *
     * @throws Throwable
     */
    public function import(SerialNumbersImportRequest $request, SerialNumberService $service): JsonResponse
    {
        $dto = SerialNumberDto::byArgs($request->validated());

        $product = Product::query()
            ->where('guid', $dto->getProductGuid())
            ->firstOrFail();

        $stats = $service->import($product, $dto);

//        $errors = collect($serials = $dto->getSerialNumbers())
//            ->diff(
//                ProductSerialNumber::query()->whereIn('serial_number', $serials)->pluck('serial_number')
//            )
//            ->values()
//            ->toArray();

        $errors = [];

        return $this->success(
            [
                'given_serial_numbers' => count($dto->getSerialNumbers()),
                'import_statistics' => [
                    'total' => $stats->getTotal(),
                    'new' => $stats->getNew(),
                    'exists' => $stats->getExists(),
                    'updated' => $stats->getUpdated(),
                ],
                'errors' => $errors
            ]
        );
    }
}
