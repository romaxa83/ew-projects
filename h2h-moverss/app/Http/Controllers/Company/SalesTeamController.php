<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\SalesTeamIndexRequest;
use App\Http\Requests\Company\SalesTeamUpdateRequest;
use Carbon\CarbonImmutable;
use App\Models\{Employee, Employee\EfficiencyPlan, Employee\SalesPlan};
use Illuminate\Http\{JsonResponse};

class SalesTeamController extends Controller
{
    public function index(SalesTeamIndexRequest $request): JsonResponse
    {
        $filter = $request->validated();

        try {
            $result = $this->getDataByDate($filter['date']);
        } catch (\Throwable $e) {
            return $this->responseErrorJson($e->getMessage());
        }

        return $this->responseDataJson([
            'success' => true,
            'data' => $result
        ]);
    }

    private function getDataByDate(null|string $date): array
    {
        if(is_null($date)) {
            return [];
        }

        $pastMonth = CarbonImmutable::now()->subMonth()->format('Y-m');
//dd(EfficiencyPlan::validDate($date));
        // efficiencyPlan
        $efficiencyPlan = EfficiencyPlan::query()
            ->whereDate('date_at', EfficiencyPlan::validDate($date))
            ->first();
        if(is_null($efficiencyPlan)) {
            $efficiencyPlan = new EfficiencyPlan();
            $efficiencyPlan->date_at = EfficiencyPlan::validDate($date);
            $efficiencyPlan->save();

        }
        $pastEfficiencyPlan = EfficiencyPlan::query()
            ->whereDate('date_at', EfficiencyPlan::validDate($pastMonth))
            ->first();

        $efficiencyPlan = [
            'id' => $efficiencyPlan->id,
            'date' => $efficiencyPlan->getDate(),
            'conversion_local_team' => $efficiencyPlan->conversion_local_team,
            'conversion_long_team' => $efficiencyPlan->conversion_long_team,
            'prev_id' => $pastEfficiencyPlan?->id,
            'prev_conversion_local_team' => $pastEfficiencyPlan?->conversion_local_team,
            'prev_conversion_long_team' => $pastEfficiencyPlan?->conversion_long_team,
        ];

        // salesPlan
        $localTeam = [];
        $longTeam = [];
        Employee::query()
            ->with(['salesPlans'])
            ->whereNotNull('sales_team')
            ->each(function (Employee $employee) use (&$localTeam, &$longTeam, $date, $pastMonth) {
                $salesPlan = $employee
                    ->salesPlans
                    ->where('date_at', EfficiencyPlan::validDate($date))
                    ->first();
                if(is_null($salesPlan)) {
                    $salesPlan = new Employee\SalesPlan();
                    $salesPlan->employee_id = $employee->id;
                    $salesPlan->date_at = EfficiencyPlan::validDate($date);
                    $salesPlan->save();
                }

                // pastSalesPlan
                $pastSalesPlan = $employee
                    ->salesPlans
                    ->where('date_at', EfficiencyPlan::validDate($pastMonth))
                    ->first();
                $teamData = [
                    'employee_id' => $employee->id,
                    'name' => $employee->name,
                    'last_name' => $employee->l_name,
                    'sales_plan_id' => $salesPlan->id,
                    'local' => $salesPlan->local,
                    'intrestate' => $salesPlan->intrestate,
                    'date' => $salesPlan->getDate(),
                    'prev_sales_plan_id' => $pastSalesPlan?->id,
                    'prev_local' => $pastSalesPlan?->local,
                    'prev_intrestate' => $pastSalesPlan?->intrestate,
                ];

                if($employee->sales_team->isLocal()){
                    $localTeam[] = $teamData;
                } else {
                    $longTeam[] = $teamData;
                }

            });

        return [
            'sales_plans' => [
                'local' => $localTeam,
                'long' => $longTeam,
            ],
            'efficiency_plan' => $efficiencyPlan,
        ];
    }

    public function update(SalesTeamUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $date = null;
        try {
            foreach ($data as $key => $item){
                if($key == 'sales_plans'){
                    foreach ($item['local'] ?? [] as $i){
                        $model = SalesPlan::find($i['sales_plan_id']);
                        if($model) {
                            $model->local = $i['local'];
                            $model->intrestate = $i['intrestate'];
                            $model->save();

                            if(is_null($date)) $date = $model->date_at;
                        }
                    }
                    foreach ($item['long'] ?? [] as $i){
                        $model = SalesPlan::find($i['sales_plan_id']);
                        if($model) {
                            $model->local = $i['local'];
                            $model->intrestate = $i['intrestate'];
                            $model->save();

                            if(is_null($date)) $date = $model->date_at;
                        }
                    }
                }

                if($key == 'efficiency_plan'){
                    $model = EfficiencyPlan::find($item['id']);
                    if ($model){
                        $model->conversion_local_team = $item['conversion_local_team'];
                        $model->conversion_long_team = $item['conversion_long_team'];
                        $model->save();

                        if(is_null($date)) $date = $model->date_at;
                    }
                }
            }

            $result = $this->getDataByDate($date);
        } catch (\Throwable $e) {
            return $this->responseErrorJson($e->getMessage());
        }

        return $this->responseDataJson([
            'success' => true,
            'data' => $result
        ]);
    }
}
