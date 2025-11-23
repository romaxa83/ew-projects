<?php

namespace App\Http\Controllers\Reports;

use App\Exports\Reports\FinancialCheckReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\FinancialCheckFilterRequest;
use App\Repositories\Reports\FinancialCheckRepository;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{JsonResponse};
use Maatwebsite\Excel\Facades\Excel;

class FinancialCheckController extends Controller
{
    public function __construct(protected FinancialCheckRepository $repo)
    {}


    /**
     * Get View List of Transactions.
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('layouts.render.with-container', [
            'component' => 'report-financial-order',
            'title' => 'Financial Check',
            'breadcrumbs' => [],
        ]);
    }

    public function report(FinancialCheckFilterRequest $request): JsonResponse
    {
        $filter = $request->validated();

        $division = session()->get('division');
        $filter['division_id'] = $division['id'] ?? (int)$filter['division_id'];
        $filter['division_tz'] = $division['miscs']['tz'] ?? $filter['division_tz'];

        $result = $this->repo->getPagination($filter);

        return response()
            ->json([
                'success' => true,
                'paginate' => $result,
            ]);
    }

    public function managers()
    {
        $users = User::query()
            ->with(['employee'])
            ->where('active', 1)
            ->whereHas('roles', function ($q) {
                $q->orderManager();
            })
            ->whereJsonContains('division_ids', [(int)session()->get('division.id')])
            ->orderBy('name')
            ->get()
        ;

        return $this->responseDataJson([
            'success' => true,
            'link' => $users
        ]);
    }

    public function exportCsv(FinancialCheckFilterRequest $request)
    {
        $filter = $request->validated();

        $division = session()->get('division');
        $filter['division_id'] = $division['id'] ?? (int)$filter['division_id'];
        $filter['division_tz'] = $division['miscs']['tz'] ?? $filter['division_tz'];

        $filename = 'financial_check_report.csv';
        $storagePath = 'app/public/reports/';
        $filepath = storage_path($storagePath);

        // если папки нет - создаем
        if (!file_exists($filepath)) {
            mkdir($filepath, 0777, true);
        }
        $file = fopen($filepath . $filename, 'w');

        try {
            $collection = $this->repo->getCollectionForExport($filter);

            $header = self::fileHeaders();

            fputcsv($file, $header);

            foreach ($collection->all() as $item) {

                fputcsv($file, $item);
            }

        } catch (\Exception $e){
            fclose($file);
            return $this->responseErrorJson(
                $e->getMessage(),
                $e->getCode(),
            );
        }

        fclose($file);

        return $this->responseDataJson([
            'success' => true,
            'link' => asset('storage/reports/' . $filename)
        ]);
    }

    public function exportExcel(FinancialCheckFilterRequest $request): JsonResponse
    {
        $filter = $request->validated();

        $division = session()->get('division');
        $filter['division_id'] = $division['id'] ?? (int)$filter['division_id'];
        $filter['division_tz'] = $division['miscs']['tz'] ?? $filter['division_tz'];

        try {
            $directory = 'public/reports';
            $files = \Storage::files($directory);
            foreach ($files as $file) {
                if (substr($file, -5) == '.xlsx') {
                    \Storage::delete($file);
                }
            }
            $collection = $this->repo->getCollectionForExport($filter);

            $filename = "reports/financial_check_report.xlsx";

            Excel::store(new FinancialCheckReportExport($collection->all()), $filename,'public');

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

    public static function fileHeaders(): array
    {
        return [
            'Order id',
            'Manager',
            'Service date',
            'Total paid',
        ];
    }
}
