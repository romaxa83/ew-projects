<?php

namespace App\Http\Controllers\API;

use App\Enums\Common\LogKeyEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vapi\EmployeeToTransferRequest;
use App\Http\Requests\Api\Vapi\RequestForClient;
use App\Models\Client;
use App\Models\Vapi\CallEvent;
use App\Services\Employees\SIPStatusService;
use App\Services\VAPI\VapiService;
use Illuminate\Http\{JsonResponse, Request};
use Exception;

class VapiController extends Controller
{
    public function __construct(protected VapiService $service)
    {}

    public function callData(Request $request): JsonResponse
    {
        try {
           $data = $request->all();

            logger_vapi('VAPI call SUCCESS', $data);
        } catch (Exception $e) {
            \Log::info('VAPI call FAIL', [$e]);

            logger_vapi('VAPI call FAIL', [$e]);
            return response()
                ->json([
                    'msg' => $e->getMessage(),
                    'success' => false,
                ]);
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }

    public function getClientByPhone(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            if(isset($data['customer_number'])){
                if (strpos($data['customer_number'], "+1") === 0) {
                    $phone = substr($data['customer_number'], 2);

                    $client = Client::query()
                        ->whereHas('phones', function ($query) use ($phone) {
                            $query->where('value', $phone);
                        })
                        ->first();


                    logger_vapi('VAPI getClientByPhone SUCCESS', [
                        'request' => $data,
                        'first_name' => $client->name,
                    ]);

                    if($client){
                        return response()
                            ->json([
                                'success' => true,
                                'first_name' => $client->name,
                            ]);
                    }
                }
            }
        } catch (Exception $e) {
            \Log::info('VAPI call FAIL', [$e]);

            logger_vapi('VAPI call FAIL', [$e]);
            return response()
                ->json([
                    'msg' => $e->getMessage(),
                    'success' => false,
                ]);
        }

        return response()
            ->json([
                'success' => false,
            ]);
    }

    // получение сотрудника на которого можно затрансферить звонок
    public function getEmployeeToTransfer(EmployeeToTransferRequest $request): JsonResponse
    {
        $data = $request->all();
        try {
            $departmentIds = [];
            if($data['department_type'] == 'support'){
                $departmentIds[] = SIPStatusService::CUSTOMER_SUPPORT_DEPARTMENT_ID;
            }
            if($data['department_type'] == 'сlaims'){
                $departmentIds[] = SIPStatusService::CLAIMS_DEPARTMENT_ID;
            }
            if($data['department_type'] == 'sales'){
                $departmentIds[] = [
                    SIPStatusService::SALES_LOCAL_DEPARTMENT_ID,
                    SIPStatusService::SALES_LONG_DEPARTMENT_ID,
                ];
            }
//            $sipUsername = SIPStatusService::getOnline($departmentIds)
//                ->getSipUsername();


            $sipUsername = null;
//            if($data['department_type'] == 'support'){
//                $sipUsername = 'h2hmoverscom_wezom_test_support';
//            }
//            if($data['department_type'] == 'сlaims'){
//                $sipUsername = 'h2hmoverscom_wezom_test';
//            }

        } catch (Exception $e) {
            \Log::error('VAPIController@getEmployeeToTransfer FAIL', [
                'input' => $data,
                'error' => $e,
            ]);

            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        \Log::info('VapiController@getEmployeeToTransfer', [
            'input' => $data,
            'sip_username' => $sipUsername,
            'department_ids' => $departmentIds
        ]);

        return $this->responseDataJson([
            'success' => true,
            'employee_sip' => $sipUsername
        ]);
    }


    public function webhook(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            if(
                isset($data['message']['type'])
                && $data['message']['type'] === CallEvent::TYPE_EVENT_END_OF_CALL
            ){
                $model = $this->service->createCallEventFromWebhook($data);

                \Log::info(LogKeyEnum::Webhook() . 'VAPIController@webhook', [
//                    'input' => $data,
                    'model' => $model,
                ]);
            }
        } catch (Exception $e) {
            \Log::error('VAPIController@webhook', [$e]);

            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return $this->responseDataJson(['success' => true]);
    }

    public function requestForClient(RequestForClient $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $model = $this->service->createClientRequest($data);

            \Log::info('VAPIController@requestForClient', [
                'input' => $data,
                'model' => $model,
            ]);
        } catch (Exception $e) {
            \Log::error(LogKeyEnum::Webhook() . 'VAPIController@requestForClient FAIL', [
                'error' => $e,
                'input' => $data,
            ]);

            return $this->responseErrorJson($e->getMessage(), $e->getCode());
        }

        return $this->responseDataJson(['success' => true]);
    }
}
