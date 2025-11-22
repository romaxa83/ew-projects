<?php


namespace App\Http\Controllers\V1\Data;


use App\Http\Controllers\ApiController;
use App\Http\Controllers\V1\Carrier\Intl\LanguageController;
use App\Http\Controllers\V1\Data\Locations\StateController;
use App\Http\Resources\Data\ReferencesResource;
use App\Models\Orders\Expense;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use App\Services\Contacts\ContactService;
use App\Services\TimezoneService;
use Spatie\Permission\Models\Role;

class ReferencesController extends ApiController
{
    /**
     * @return ReferencesResource
     *
     * @OA\Get(
     *     path="/v1/data/references",
     *     tags={"V1 Data References"},
     *     summary="References",
     *     operationId="References",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ReferencesResource")
     *     ),
     * )
     *
     */
    public function index(): ReferencesResource
    {
        return ReferencesResource::make(
            [
                'languages' => LanguageController::getLanguagesList(),
                'statesList' => StateController::getStatesList(),
                'contactTypes' => resolve(ContactService::class)->getContactTypesForContacts(),
                'timezoneList' => resolve(TimezoneService::class)->getTimezonesList(),
                'paymentMethods' => Payment::getMethodsList(),
                'vehicleTypes' => Vehicle::getTypesList(),
                'expenseTypes' => Expense::getTypesList(),
                'roles' => Role::query()->select('id', 'name', 'guard_name')->get()->all(),
            ]
        );
    }
}
