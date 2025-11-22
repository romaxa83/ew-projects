<?php

namespace App\Http\Controllers\Api\Payrolls;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Payrolls\DeleteManyRequest;
use App\Http\Requests\Payrolls\MarkAsPaidRequest;
use App\Http\Requests\Payrolls\PayrollRequest;
use App\Http\Requests\Payrolls\PreparePayrollRequest;
use App\Http\Resources\Payrolls\PayrollPaginatedResource;
use App\Http\Resources\Payrolls\PayrollResource;
use App\Models\Orders\Expense;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Payrolls\Payroll;
use App\Models\Saas\Company\Company;
use App\Notifications\SendPdfPayroll;
use App\Scopes\CompanyScope;
use App\Services\Events\EventService;
use App\Services\Orders\GeneratePdfService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Log;
use Str;
use Throwable;
use Twilio\TwiML\Voice\Pay;

class PayrollController extends ApiController
{
    /**
     * @param Payroll $payroll
     * @return JsonResponse
     * @throws AuthorizationException|Throwable
     *
     * @OA\Get(
     *     path="/api/payrolls/{payrollId}/send-pdf",
     *     tags={"Payrolls"},
     *     summary="Send payroll to driver email",
     *     operationId="Send payroll to driver email",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",),
     * )
     */
    public function sendPdf(Payroll $payroll): JsonResponse
    {
        $this->authorize('payrolls create');

        try {
            $company = Company::find($payroll->carrier_id);

            if (!$company) {
                return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
            }

            $pdfService = resolve(GeneratePdfService::class);

            $pdf = $pdfService->template2pdf(
                'pdf.payroll',
                [
                    'payroll' => $payroll->load(
                        [
                            'orders' => function ($query) {
                                $query->orderBy('delivery_date_actual', 'desc');
                            },
                            'orders.payment',
                            'orders.vehicles'
                        ]
                    ),
                    'profile' => $company->getProfileData(),
                    'billingContacts' => $company->getBillingContactsAsString(),
                    'billing_payment_details' => $company->getBillingPaymentDetails(),
                    'driver_salary_contact_info' => $company->driver_salary_contact_info ?? [],
                    'stateNames' => $payroll->getStateNamesArr(),
                ],
                false
            );

            Notification::send(
                $payroll->driver,
                new SendPdfPayroll(
                    $company,
                    $payroll,
                    $pdf
                )
            );

            $payroll->update(['send_pdf_at' => CarbonImmutable::now()]);

            return $this->makeSuccessResponse();
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
            return $this->makeErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return string|null
     * @throws Throwable
     */
    public function getPdf(Request $request)
    {
        if (!$request->public_token) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        /** @var $payroll Payroll */
        $payroll = Payroll::withoutGlobalScope(CompanyScope::class)
            ->where('public_token', $request->public_token)
            ->first();

        if (!$payroll) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        $company = Company::find($payroll->carrier_id);

        if (!$company) {
            return $this->makeErrorResponse(null, Response::HTTP_NOT_FOUND);
        }

        $pdfService = resolve(GeneratePdfService::class);

//        dd($payroll->orders[0]->paymentStages);

        return $pdfService->template2pdf(
            'pdf.payroll',
            [
                'payroll' => $payroll->load(
                    [
                        'orders' => function ($query) {
                            $query->orderBy('delivery_date_actual');
                        },
                        'orders.payment',
                        'orders.paymentStages',
                        'orders.vehicles'
                    ]
                ),
                'profile' => $company->getProfileData(),
                'billingContacts' => $company->getBillingContactsAsString(),
                'billing_payment_details' => $company->getBillingPaymentDetails(),
                'stateNames' => $payroll->getStateNamesArr(),
                'driver_salary_contact_info' => $company->driver_salary_contact_info ?? [],
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/payrolls",
     *     tags={"Payrolls"},
     *     summary="Get payrolls paginated list",
     *     operationId="Get payrolls data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="load_id", in="query", description="", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="driver_id", in="query", description="", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="role_id", in="query", description="", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="not_paid", in="query", description="", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Records per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field to sort by", required=false,
     *          @OA\Schema(type="string", default="id", enum ={"id"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Sort order", required=false,
     *          @OA\Schema(type="string", default="asc",enum ={"asc","desc"})
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PayrollPaginatedResource")
     *     ),
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('payrolls');

        $orderBy = 'id';
        $orderByType = in_array($request->input('order_type'), ['asc', 'desc']) ? $request->input('order_type') : 'desc';
        $perPage = (int) $request->input('per_page', 10);

        $data = Payroll::filter($request->only(['load_id', 'driver_id', 'date_from', 'date_to', 'not_paid', 'role_id']))
            ->orderBy($orderBy, $orderByType)
            ->paginate($perPage);

        return PayrollPaginatedResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PayrollRequest $request
     * @return PayrollResource
     * @throws AuthorizationException
     *
     * @OA\Post(
     *     path="/api/payrolls",
     *     tags={"Payrolls"},
     *     summary="Create payroll",
     *     operationId="Create payroll",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *              @OA\Schema (
     *                  type="object",
     *                  required={
     *                      "start",
     *                      "end",
     *                      "driver_id",
     *                      "driver_rate",
     *                      "total",
     *                      "subtotal",
     *                      "salary",
     *                      "commission",
     *                      "orders"
     *                  },
     *                  @OA\Property (
     *                      property="start",
     *                      type="string",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="end",
     *                      type="string",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="notes",
     *                      type="string",
     *                      nullable=true,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="driver_id",
     *                      type="integer",
     *                      nullable=false,
     *                      description="Driver ID"
     *                  ),
     *                  @OA\Property (
     *                      property="driver_rate",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="total",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="subtotal",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="salary",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="commission",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="orders",
     *                      type="array",
     *                      nullable=false,
     *                      description="Orders list",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "id",
     *                                      "load_id",
     *                                  },
     *                                  @OA\Property (property="id", type="integer", description="Order ID"),
     *                                  @OA\Property (property="load_id", type="string", description="Order load ID"),
     *                              )
     *                          }
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="expenses_before",
     *                      type="array",
     *                      nullable=true,
     *                      description="",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "type",
     *                                      "price",
     *                                  },
     *                                  @OA\Property (property="type", type="string", nullable=false),
     *                                  @OA\Property (property="price", type="number", format="float", nullable=false),
     *                              )
     *                          }
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="expenses_after",
     *                      type="array",
     *                      nullable=true,
     *                      description="",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "type",
     *                                      "price",
     *                                  },
     *                                  @OA\Property (property="type", type="string", nullable=false),
     *                                  @OA\Property (property="price", type="number", format="float", nullable=false),
     *                              )
     *                          }
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="bonuses",
     *                      type="array",
     *                      nullable=true,
     *                      description="",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "type",
     *                                      "price",
     *                                  },
     *                                  @OA\Property (property="type", type="string", nullable=false),
     *                                  @OA\Property (property="price", type="number", format="float", nullable=false),
     *                              )
     *                          }
     *                      )
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PayrollResource")
     *     ),
     * )
     */
    public function store(PayrollRequest $request): PayrollResource
    {
        $this->authorize('payrolls create');

        $payroll = new Payroll();
        $payroll->fill($request->validated());
        $payroll->public_token = hash('sha256', Str::random(60));
        $payroll->save();

        $payroll->orders()->sync($request->orders());

        EventService::payroll($payroll)
            ->user($request->user())
            ->create()
            ->broadcast();

        return PayrollResource::make($payroll);
    }

    /**
     * Display the specified resource.
     *
     * @param Payroll $payroll
     * @return PayrollResource
     * @throws AuthorizationException
     *
     * @OA\Get(
     *     path="/api/payrolls/{payrollId}",
     *     tags={"Payrolls"},
     *     summary="Get payroll info",
     *     operationId="Get payroll data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PayrollResource")
     *     ),
     * )
     */
    public function show(Payroll $payroll): PayrollResource
    {
        $this->authorize('payrolls read');

        return PayrollResource::make($payroll);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PayrollRequest $request
     * @param Payroll $payroll
     * @return JsonResponse|PayrollResource
     * @throws AuthorizationException
     * @OA\Put(
     *     path="/api/payrolls/{payrollId}",
     *     tags={"Payrolls"},
     *     summary="Update payroll",
     *     operationId="Update payroll",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *              @OA\Schema (
     *                  type="object",
     *                  required={
     *                      "driver_id",
     *                      "driver_rate",
     *                      "total",
     *                      "subtotal",
     *                      "salary",
     *                      "commission",
     *                      "orders"
     *                  },
     *                  @OA\Property (
     *                      property="notes",
     *                      type="string",
     *                      nullable=true,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="driver_id",
     *                      type="integer",
     *                      nullable=false,
     *                      description="Driver ID"
     *                  ),
     *                  @OA\Property (
     *                      property="driver_rate",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="total",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="subtotal",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="salary",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="commission",
     *                      type="number",
     *                      format="float",
     *                      nullable=false,
     *                      description=""
     *                  ),
     *                  @OA\Property (
     *                      property="orders",
     *                      type="array",
     *                      nullable=false,
     *                      description="Orders list",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "id",
     *                                      "load_id",
     *                                  },
     *                                  @OA\Property (property="id", type="integer", description="Order ID"),
     *                                  @OA\Property (property="load_id", type="string", description="Order load ID"),
     *                              )
     *                          }
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="expenses_before",
     *                      type="array",
     *                      nullable=true,
     *                      description="",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "type",
     *                                      "price",
     *                                  },
     *                                  @OA\Property (property="type", type="string", nullable=false),
     *                                  @OA\Property (property="price", type="number", format="float", nullable=false),
     *                              )
     *                          }
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="expenses_after",
     *                      type="array",
     *                      nullable=true,
     *                      description="",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "type",
     *                                      "price",
     *                                  },
     *                                  @OA\Property (property="type", type="string", nullable=false),
     *                                  @OA\Property (property="price", type="number", format="float", nullable=false),
     *                              )
     *                          }
     *                      )
     *                  ),
     *                  @OA\Property (
     *                      property="bonuses",
     *                      type="array",
     *                      nullable=true,
     *                      description="",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "type",
     *                                      "price",
     *                                  },
     *                                  @OA\Property (property="type", type="string", nullable=false),
     *                                  @OA\Property (property="price", type="number", format="float", nullable=false),
     *                              )
     *                          }
     *                      )
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PayrollResource")
     *     ),
     * )
     */
    public function update(PayrollRequest $request, Payroll $payroll)
    {
        $this->authorize('payrolls update');

        if ($payroll->is_paid) {
            return $this->makeErrorResponse(null, Response::HTTP_FORBIDDEN);
        }

        $payroll->fill($request->validated());
        $payroll->save();

        $payroll->orders()->sync($request->orders());

        EventService::payroll($payroll)
            ->user($request->user())
            ->update()
            ->broadcast();

        return PayrollResource::make($payroll);
    }

    /**
     * @param MarkAsPaidRequest $request
     * @return JsonResponse
     *
     * @OA\Put(
     *     path="/api/payrolls/mark-as-paid",
     *     tags={"Payrolls"},
     *     summary="Mark payrolls paid",
     *     operationId="Mark payrolls paid",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function markAsPaid(MarkAsPaidRequest $request): JsonResponse
    {
        $payrolls = Payroll::whereIn('id', $request->payrolls())
            ->where('is_paid', false);

        $user = $request->user();

        $payrolls->get()->map(
            function (Payroll $payroll) use ($user) {
                EventService::payroll($payroll)
                    ->user($user)
                    ->paid()
                    ->broadcast();
            }
        );

        $payrolls->update(
            [
                'is_paid' => true,
                'paid_at' => now()->timestamp,
            ]
        );

        return $this->makeSuccessResponse(null, Response::HTTP_OK);
    }

    /**
     * @param DeleteManyRequest $request
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/payrolls/delete-many",
     *     tags={"Payrolls"},
     *     summary="Delete many payrolls",
     *     operationId="Delete many payrolls",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="",
     *          required=true,
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function deleteMany(DeleteManyRequest $request): JsonResponse
    {
        $payrolls = Payroll::whereIn('id', $request->payrolls());

        $user = $request->user();

        $payrolls->get()->map(
            function (Payroll $payroll) use ($user) {
                EventService::payroll($payroll)
                    ->user($user)
                    ->delete()
                    ->broadcast();
            }
        );

        $payrolls->delete();

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param PreparePayrollRequest $request
     * @return PayrollResource
     *
     * @OA\Post(
     *     path="/api/payrolls/prepare",
     *     tags={"Payrolls"},
     *     summary="Prepare payroll",
     *     operationId="Prepare payroll",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *             mediaType="application/json",
     *              @OA\Schema (
     *                  type="object",
     *                  required={
     *                      "driver_id",
     *                      "orders"
     *                  },
     *                  @OA\Property (
     *                      property="driver_id",
     *                      type="integer",
     *                      nullable=false,
     *                      description="Driver ID"
     *                  ),
     *                  @OA\Property (
     *                      property="orders",
     *                      type="array",
     *                      nullable=false,
     *                      description="Orders list",
     *                      @OA\Items (
     *                          allOf={
     *                              @OA\Schema (
     *                                  type="object",
     *                                  required={
     *                                      "id",
     *                                      "load_id",
     *                                  },
     *                                  @OA\Property (property="id", type="integer", description="Order ID"),
     *                                  @OA\Property (property="load_id", type="string", description="Order load ID"),
     *                              )
     *                          }
     *                      )
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PayrollResource")
     *     ),
     * )
     */
    public function prepare(PreparePayrollRequest $request): PayrollResource
    {
        $payroll = new Payroll(['driver_id' => $request->driverId()]);

        $payroll->driver_rate = $payroll->driver->driverInfo->driver_rate ?? null;

        $price = Payment::query()
            ->whereIn('order_id', $request->orders())
            ->sum('total_carrier_amount');


        $payroll->total = $price;

        $expensesTotal = 0;

        Expense::query()
            ->leftJoin('orders', 'orders.id', '=', 'expenses.order_id')
            ->selectRaw('orders.load_id, expenses.*')
            ->whereIn('order_id', $request->orders())
            ->get()
            ->transform(
                function ($el) use (&$expensesTotal) {
                    $expensesTotal += (double)$el->price;
                    return [
                        'load_id' => $el->load_id,
                        'type' => isset($el->type_id) ? Expense::EXPENSE_TYPES[$el->type_id] : null,
                        'price' => (double) $el->price,
                        'date' => $el->date,
                    ];
                }
            );

        $payroll->subtotal = $payroll->total - $expensesTotal;

        $deductedOrders = $this->getDeductedOrdersAsExpenses($request->orders());

        if (isset($deductedOrders) && is_array($deductedOrders)) {
            $payroll->expenses_after = array_merge(
                $payroll->expenses_after ?? [],
                $deductedOrders
            );
        }

        return PayrollResource::make($payroll);
    }

    private function getDeductedOrdersAsExpenses(array $orderIDs): array
    {
        return Order::where('deduct_from_driver', true)
            ->whereIn(Order::TABLE_NAME . '.id', $orderIDs)
            ->get()
            ->map(
                function (Order $order) {
                    $price = isset($order->payment->price) ? (double)$order->payment->price : 0;

                    $order->expenses->map(
                        function ($e) use (&$price) {
                            $price += (double)$e->price;
                        }
                    );

                    return [
                        'load_id' => $order->load_id,
                        'type' => '#' . $order->load_id,
                        'price' => $price,
                        'date' => null,
                        'note' => $order->deducted_note
                    ];
                }
            )
            ->all();
    }

}
