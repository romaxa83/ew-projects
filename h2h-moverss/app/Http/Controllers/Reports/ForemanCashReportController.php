<?php

namespace App\Http\Controllers\Reports;

use App\Exports\Reports\FinancialCheckReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportForemanCashRequest;
use App\Models\CashRegistry\CashRegistry;
use App\Models\CashRegistry\CashRegistryItem;
use App\Services\CashRegistry\CashRegistryService;
use App\Services\Payrolls\PayrollService;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{JsonResponse};
use Maatwebsite\Excel\Facades\Excel;

class ForemanCashReportController extends Controller
{
    public function __construct(
        protected PayrollService $payrollService,
        protected CashRegistryService $cashRegistryService,
    )
    {}

    /**
     * Get View List of Transactions.
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('layouts.render.clean', [
            'component' => 'report-foreman-cash',
        ]);
    }

    public function report(ReportForemanCashRequest $request): JsonResponse
    {
        $filter = $request->validated();

        $result = $this->payrollService
            ->getPagination($filter);

        $meta = [
            'previous_balance' => 0,
            'balance_end_period' => 0,
        ];
        if(
            isset($filter['employee_id'])
            && isset($filter['start_range'])
            && isset($filter['end_range'])
        ){
            $meta = $this->cashRegistryService->getBalance(
                $filter['start_range'],
                $filter['end_range'],
                $filter['employee_id'],
            );
        }

        return response()
            ->json([
                'success' => true,
                'paginate' => $result,
                'meta' => $meta,
            ]);
    }

    public function foremans()
    {
        $users = User::query()
            ->with(['employee'])
            ->where('active', 1)
            ->whereHas('roles', function ($q) {
                $q->foreman();
            })
            ->whereHas('employee', function ($q) {
                $q->whereJsonContains('division_ids', [(int)session()->get('division.id')]);
            })
            ->orderBy('name')
            ->get()
        ;

        $result = [];
        foreach ($users as $user) {
            $result[$user->employee->id] = $user->employee->full_name;
        }

        return $this->responseDataJson([
            'success' => true,
            'data' => $result
        ]);
    }

    public function exportCsv(ReportForemanCashRequest $request)
    {
        $filter = $request->validated();

        $filename = 'foreman_cash_report.csv';
        $storagePath = 'app/public/reports/';
        $filepath = storage_path($storagePath);

        // если папки нет - создаем
        if (!file_exists($filepath)) {
            mkdir($filepath, 0777, true);
        }
        $file = fopen($filepath . $filename, 'w');

        try {
            $collection = $this->payrollService->getCollection($filter);

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

    public function exportExcel(ReportForemanCashRequest $request): JsonResponse
    {
        $filter = $request->validated();

        try {
            $directory = 'public/reports';
            $files = \Storage::files($directory);
            foreach ($files as $file) {
                if (substr($file, -5) == '.xlsx') {
                    \Storage::delete($file);
                }
            }
            $collection = $this->payrollService->getCollection($filter);

            $filename = "reports/foreman_cash_report.xlsx";

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
