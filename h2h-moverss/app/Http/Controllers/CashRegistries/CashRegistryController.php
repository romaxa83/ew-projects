<?php

namespace App\Http\Controllers\CashRegistries;

use App\Enums\CashRegistry\OperationType;
use App\Exports\CashRegistry\OperationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\CashRegistry\AddOperationRequest;
use App\Http\Requests\CashRegistry\OperationFilterRequest;
use App\Models\CashRegistry\CashRegistry;
use App\Services\CashRegistry\CashRegistryService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class CashRegistryController extends Controller
{
    public function __construct(protected CashRegistryService $service)
    {}

    public function renderOperation(): Renderable
    {
        return view('layouts.render.component', [
            'component' => 'cash-registry-operations',
            'title' => null,
            'mixed' => ['/js/flatpicker-plugins.js'],
            'assets' => ['/css/flatpicker.css'],
        ]);
    }

    public function renderFormans(): Renderable
    {
        return view('layouts.render.clean', [
            'component' => 'cash-registry-foremans',
        ]);
    }

    public function records(): JsonResponse
    {
        $divisionId = session()->get('division.id');

        $records = CashRegistry::query()
            ->with('employee:id,name,l_name')
            ->where('active', true)
            ->whereHas('employee', function ($query) use ($divisionId) {
                $query->whereJsonContains('division_ids', $divisionId);
            })
            ->get();

        $meta = [
            'total' => round($records->sum('cash_on_hand'), 2),
        ];

        return response()
            ->json([
                'success' => true,
                'records' => $records,
                'meta' => $meta,
            ]);
    }

    public function getOperations(OperationFilterRequest $request): JsonResponse
    {

        $filter = $request->validated();
        $filter['division_id'] = session()->get('division.id');

        return response()
            ->json([
                'success' => true,
                'records' => $this->service->getPagination($filter),
            ]);
    }

    public function addOperation(AddOperationRequest $request): JsonResponse
    {
        $this->service->addOperation($request->validated());

        $records = CashRegistry::query()
            ->with('employee:id,name,l_name')
            ->where('active', true)
            ->get();

        $meta = [
            'total' => round($records->sum('cash_on_hand'), 2),
        ];

        return response()
            ->json([
                'success' => true,
                'records' => $records,
                'meta' => $meta,
            ]);
    }

    public function operationTypes(): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'records' => [
                    'for_form' => OperationType::forForm(),
                    'for_filter' => OperationType::forFilter(),
                ],
            ]);
    }

    public function exportExcel(OperationFilterRequest $request): JsonResponse
    {
        $filter = $request->validated();
        $filter['division_id'] = session()->get('division.id');

        try {
            $directory = 'public/cash-registry';
            $files = \Storage::files($directory);
            foreach ($files as $file) {
                if (substr($file, -5) == '.xlsx') {
                    \Storage::delete($file);
                }
            }
            $collection = $this->service->getCollection($filter);

            $filename = "cash_registry_operations.xlsx";

            Excel::store(new OperationExport($collection->all()), $filename, 'public');

        }catch (\Exception $e){
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        return $this->responseDataJson([
            'success' => true,
            'link' => asset('storage/' . $filename)
        ]);
    }
}
