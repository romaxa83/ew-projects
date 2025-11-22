<?php

namespace App\Http\Controllers\Api\Parsers;

use App\Exceptions\Parser\EmptyVehiclesException;
use App\Exceptions\Parser\PdfFileException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Orders\UploadPdfOrderRequest;
use App\Http\Resources\Parsers\ParsedPdfResource;
use App\Services\Parsers\PdfNormalizeService;
use App\Services\Parsers\PdfService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Log;
use Throwable;

class PdfOrderController extends ApiController
{
    /**
     * @param UploadPdfOrderRequest $request
     * @param PdfService $pdfService
     * @param PdfNormalizeService $pdfNormalizeService
     * @return ParsedPdfResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function parse(
        UploadPdfOrderRequest $request,
        PdfService $pdfService,
        PdfNormalizeService $pdfNormalizeService
    ) {

        dd(
//            $pdfNormalizeService->normalizeAfterParsing(
                $pdfService->process(
                    $request->file('order_pdf')
//                )
            )
        );


        try {
            return ParsedPdfResource::make(
                $pdfNormalizeService->normalizeAfterParsing(
                    $pdfService->process(
                        $request->file('order_pdf')
                    )
                )
            );
        } catch (PdfFileException | EmptyVehiclesException $e) {
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_NOT_ACCEPTABLE);
        } catch (Exception $e) {

//            dd($e->getMessage());

            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
