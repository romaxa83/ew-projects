<?php

namespace App\Http\Controllers\Partners;

use App\DataTables\Partners\PartnerRecordDataTable;
use App\Dto\Partners\PartnerDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partners\PartnerRequest;
use App\Models\Division;
use App\Models\Partners\Partner;
use App\Services\Partners\PartnerService;
use Illuminate\Http\JsonResponse;

class CrudController extends Controller
{

    public function __construct(protected PartnerService $partnerService)
    {}


    /**
     * Get records for DT.
     * @param  PartnerRecordDataTable  $dataTable
     * @return mixed
     */
    public function index(PartnerRecordDataTable $dataTable)
    {
        return $dataTable->render('layouts.partners.index');
    }


    /**
     * test @see \Tests\Feature\Partners\CreateTest
     */
    public function create(PartnerRequest $request)
    {
        $data = PartnerDto::byRequest($request);

        $model = $this->partnerService->create($data);

        return redirect()
            ->route('partner.show', ['id' => $model->id]);
    }

    /**
     * test @see \Tests\Feature\Partners\UpdateTest
     */
    public function update(PartnerRequest $request, $id): JsonResponse
    {
        try {
            $model = $this->partnerService->update(
                Partner::findOrFail($id),
                PartnerDto::byRequest($request)
            );

        } catch (\Throwable $e) {
            return $this->responseErrorJson($e->getMessage());
        }

        return response()
            ->json([
                'success' => true,
                'msg' => 'Partner changed',
                'record' => $model,
            ]);
    }


    /**
     * test @see \Tests\Feature\Partners\AjaxInfoTest
     */
    public function ajaxInfo($id): JsonResponse
    {
        $model = Partner::query()
            ->where('id', $id)
            ->first()
        ;

        return response()
            ->json([
                'success' => true,
                'record' => $model,
                'divisions' => Division::get(['id', 'title'])->keyBy('id'),
            ]);
    }
}
