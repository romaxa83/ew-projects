<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Payrolls\UpdatePayrollRequest;
use App\Models\Order\Payroll\Payroll;
use App\Services\Payrolls\PayrollService;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService)
    {}

    public function markAsProcess($id)
    {
        try {
            /** @var $model Payroll */
            $model = Payroll::query()
                ->findOrFail($id);
            $model = $this->payrollService->toggleProcess($model);

        } catch (\Throwable $e) {
            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return $this->responseDataJson([
            'success' => true,
            'record' => $this->payrollService->formatDataForCrm($model),
        ]);
    }

    public function update(UpdatePayrollRequest $request ,$id)
    {
        $data = $request->validated();

        /** @var $model Payroll */
        $model = Payroll::query()->findOrFail($id);

        if($model->is_processed){
            return $this->responseErrorJson("Payroll in processed status", 400);
        }

        $result = $this->payrollService->update($model, $data);

        return $this->responseDataJson([
            'success' => true,
            'record' => $this->payrollService->formatDataForCrm($result),
        ]);
    }
}

