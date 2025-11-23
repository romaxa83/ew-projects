<?php

namespace App\Http\Controllers\API;

use App\Enums\Common\DateFormat;
use App\Enums\Common\LogKeyEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CashRegistry\CashRegistryItemsFilterRequest;
use App\Http\Requests\Api\Payroll\PayrollFilterRequest;
use App\Http\Requests\Api\Payroll\PayrollStoreRequest;
use App\Http\Resources\API\Employees\Foremans\PayrollResource;
use App\Http\Resources\API\Employees\Foremans\TransactionResource;
use App\ModelFilters\Orders\PayrollFilter;
use App\Models\CashRegistry\CashRegistryItem;
use App\Models\Employee;
use App\Models\Order\Payroll\Payroll;
use App\Services\CashRegistry\CashRegistryService;
use App\Services\Payrolls\PayrollService;
use Carbon\CarbonImmutable;
use Illuminate\Http\{JsonResponse, Resources\Json\AnonymousResourceCollection};
use Auth;

class PayrollController extends Controller
{

    public function __construct(protected CashRegistryService $cashRegistryService)
    {
    }

    public function storePayroll(PayrollStoreRequest $request, $id): JsonResponse
    {
        try {
            if(Payroll::where('order_id', $id)->exists()){
                throw new \Exception(
                    "For this order already exists payroll.",
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            \Log::info(LogKeyEnum::Api(). " STORE Payrolls for order #".$id, [
                'data' => $request->validated(),
            ]);

            /** @var $service PayrollService */
            $service = resolve(PayrollService::class);
            $payroll = $service->createForRequest($request, $id);

        } catch (\Throwable $e) {
            \Log::error(LogKeyEnum::Api(). " STORE Payrolls FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        \Log::info(LogKeyEnum::Api(). " STORE Payrolls SUCCESS create for order #".$id, [
            'payroll' => $payroll,
        ]);

        return response()
            ->json([
                'orders' => $payroll,
            ]);
    }

    public function getPayrolls(
        PayrollFilterRequest $request
    ): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filter = $request->validated();
            $filter['type'] = PayrollFilter::TYPE_PROCESSED;
            if(!isset($filter['date'])){
                $filter['date'] = CarbonImmutable::now()->format(DateFormat::FILTER_DATE());
            }
            $filter['employee_id'] = Auth::user()->employee->id;

            \Log::info(LogKeyEnum::Api(). " GET Payrolls", [
                'filter' => $filter,
            ]);

            $payrolls = Payroll::query()
                ->with([
                    'order.mobileEstimate',
                    'items.employee',
                    'items.role',
                ])
                ->filter($filter)
                ->get();
        } catch (\Throwable $e) {
            \Log::error(LogKeyEnum::Api(). " GET Payrolls FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return PayrollResource::collection($payrolls)
            ->additional([
                'meta' => $this->cashRegistryService->getBalance(
                    $filter['date'],
                    $filter['date'],
                    $filter['employee_id'],
                )
            ])
            ;
    }

    public function getTransactions(
        CashRegistryItemsFilterRequest $request
    ): AnonymousResourceCollection|JsonResponse
    {

        try {
            $filter = $request->validated();
            $filter['employee_id'] = Auth::user()?->employee?->id;

            if(!isset($filter['end_date'])){
                $filter['end_date'] = CarbonImmutable::now()->format(DateFormat::FILTER_DATE());
            }

            if(!isset($filter['start_date'])){
                $item = CashRegistryItem::query()
                    ->select(CashRegistryItem::TABLE.'.insert_date')
                    ->whereHas('foreman', function ($query) use ($filter) {
                        $query->where(Employee::TABLE. '.id', $filter['employee_id']);
                    })
                    ->first();

                $filter['start_date'] = $item
                    ? $item->insert_date->format(DateFormat::FILTER_DATE())
                    : CarbonImmutable::now()->startOfDay()->format(DateFormat::FILTER_DATE())
                ;
            }

            \Log::info(LogKeyEnum::Api(). " GET Transactions", [
                'filter' => $filter,
            ]);
            
            $items = CashRegistryItem::query()
                ->filter($filter)
                ->orderBy('insert_date', 'desc')
                ->paginate(
                    page: $filter['page'] ?? 1,
                    perPage: $filter['per_page'] ?? 20,
                );
        }
        catch (\Throwable $e) {
            \Log::error(LogKeyEnum::Api(). " GET Transactions FAIL", [$e]);
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return TransactionResource::collection($items)
            ->additional([
                'meta' => $this->cashRegistryService->getBalance(
                    $filter['start_date'],
                    $filter['end_date'],
                    $filter['employee_id'],
                )
            ])
            ;
    }
}

