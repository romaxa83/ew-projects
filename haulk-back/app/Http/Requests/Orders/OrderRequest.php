<?php

namespace App\Http\Requests\Orders;

use App\Dto\Orders\OrderDto;
use App\Http\Controllers\Api\Helpers\DateTimeHelper;
use App\Http\Requests\Contacts\ContactRequest;
use App\Models\Orders\Bonus;
use App\Models\Orders\Expense;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Rules\ExistsRule;
use App\Rules\Users\UserRoleRule;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    use ContactTransformerTrait;
    use OnlyValidateForm;
    use ValidationRulesTrait;

    private const PICKUP_CONTACT_KEY = 'pickup';
    private const DELIVERY_CONTACT_KEY = 'delivery';
    private const SHIPPER_CONTACT_KEY = 'shipper';

    private const CONTACTS_KEYS = [
        self::PICKUP_CONTACT_KEY,
        self::DELIVERY_CONTACT_KEY,
        self::SHIPPER_CONTACT_KEY
    ];

    private bool $requiredDriverAndDispatcher = false;
    private bool $copyShipper;

    private bool $saveDeliveryContact;
    private bool $savePickupContact;
    private bool $saveShipperContact;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->copyShipper = $this->boolean('shipper_copy_delivery');
        $this->saveDeliveryContact = $this->boolean('delivery_save_contact');
        $this->savePickupContact = $this->boolean('pickup_save_contact');
        $this->saveShipperContact = $this->boolean('shipper_save_contact');

        $this->merge(
            [
                'need_review' => $this->boolean('need_review'),
                'shipper_copy_delivery' => $this->copyShipper
            ]
        );

        foreach (self::CONTACTS_KEYS as $contact) {
            $this->transform($contact . '_contact');
        }

        $this->preparePayment();

        /**@var Order|null $order */
        $order = $this->route()->parameter('order');
        if (!$order) {
            return;
        }
        if ($order->ifNullDriverOrDispatcherAllowed()) {
            return;
        }
        $this->requiredDriverAndDispatcher = true;
    }

    private function preparePayment(): void
    {
        $payment = $this->input('payment');
        if (empty($payment['customer_payment_amount'])) {
            $payment['customer_payment_amount'] = null;
            $payment['customer_payment_method_id'] = null;
            $payment['customer_payment_location'] = null;
        }
        if (empty($payment['broker_payment_amount'])) {
            $payment['broker_payment_amount'] = null;
            $payment['broker_payment_method_id'] = null;
            $payment['broker_payment_days'] = null;
            $payment['broker_payment_begins'] = null;
        }
        if (empty($payment['broker_fee_amount'])) {
            $payment['broker_fee_amount'] = null;
            $payment['broker_fee_method_id'] = null;
            $payment['broker_fee_days'] = null;
            $payment['broker_fee_begins'] = null;
        }
        $this->merge(
            [
                'payment' => $payment
            ]
        );
    }

    public function rules(): array
    {
        return array_merge(
            [
                'load_id' => [
                    'required',
                    'string',
                    'min:2',
                    'max:255',
                ],
                'dispatcher_id' => [
                    $this->requiredDriverAndDispatcher ? 'required' : 'nullable',
                    'required_with:driver_id',
                    'int',
                    new ExistsRule(User::class),
                    new UserRoleRule(UserRoleRule::DISPATCHER),
                ],
                'driver_id' => [
                    $this->requiredDriverAndDispatcher ? 'required' : 'nullable',
                    'int',
                    new ExistsRule(User::class),
                    new UserRoleRule(UserRoleRule::DRIVER),
                ],
                'inspection_type' => [
                    'required',
                    'int',
                    Rule::in(array_keys(Order::INSPECTION_TYPES))
                ],
                'instructions' => [
                    'nullable',
                    'string'
                ],
                'dispatch_instructions' => [
                    'nullable',
                    'string'
                ],
                'pickup_save_contact' => [
                    'nullable'
                ],
                'delivery_save_contact' => [
                    'nullable'
                ],
                'shipper_save_contact' => [
                    'nullable'
                ],
                'need_review' => [
                    'nullable',
                    'bool'
                ],
                Order::ATTACHMENT_FIELD_NAME . '.*' => [
                    'nullable',
                    'file',
                    $this->orderAttachmentTypes()
                ],
                self::PICKUP_CONTACT_KEY . '_contact' => [
                    'required',
                    'array'
                ],
                self::DELIVERY_CONTACT_KEY . '_contact' => [
                    'required',
                    'array'
                ],
                'pickup_buyer_name_number' => [
                    'nullable',
                    'string',
                    'max:255'
                ],
                'vehicles' => [
                    'required',
                    'array'
                ],
                'vehicles.*' => [
                    'required',
                    'array'
                ],
                'vehicles.*.id' => [
                    'nullable',
                    'int',
                    Rule::exists(Vehicle::class, 'id')
                ],
                'vehicles.*.inop' => [
                    'nullable',
                    'bool',
                ],
                'vehicles.*.enclosed' => [
                    'nullable',
                    'bool',
                ],
                'vehicles.*.vin' => [
                    'nullable',
                    'string',
                    'regex:/^[a-z0-9]+$/i'
                ],
                'vehicles.*.year' => [
                    'nullable',
                    'string',
                    'regex:/^\d{1,4}$/'
                ],
                'vehicles.*.make' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'vehicles.*.model' => [
                    'required',
                    'string',
                    'max:255'
                ],
                'vehicles.*.type_id' => [
                    'required',
                    'int',
                    Rule::in(array_keys(Vehicle::VEHICLE_TYPES))
                ],
                'vehicles.*.color' => [
                    'nullable',
                    'string',
                    'max:255'
                ],
                'vehicles.*.license_plate' => [
                    'nullable',
                    'string',
                    'max:255'
                ],
                'vehicles.*.odometer' => [
                    'nullable',
                    'numeric'
                ],
                'vehicles.*.stock_number' => [
                    'nullable',
                    'string',
                    'max:255'
                ],
                'payment' => [
                    'required',
                    'array',
                ],
                'expenses' => [
                    'nullable',
                    'array'
                ],
                'expenses.*' => [
                    'required_with:expenses',
                    'array'
                ],
                'expenses.*.id' => [
                    'nullable',
                    'int',
                    new ExistsRule(Expense::class, 'id')
                ],
                'expenses.*.type_id' => [
                    'required_with:expenses',
                    'int',
                    Rule::in(array_keys(Expense::EXPENSE_TYPES))
                ],
                'expenses.*.price' => [
                    'required_with:expenses',
                    'numeric'
                ],
                'expenses.*.date' => [
                    'required_with:expenses',
                    'date_format:' . DateTimeHelper::DATE_FORMAT
                ],
                'expenses.*.' . Expense::RECEIPT_FIELD_NAME => [
                    'nullable',
                    'file'
                ],
                'expenses.*.to' => [
                    'nullable',
                    'string',
                    Rule::in([
                        Payment::PAYER_BROKER,
                        Payment::PAYER_CUSTOMER,
                        Payment::PAYER_NONE
                    ])
                ],
                'bonuses' => [
                    'nullable',
                    'array'
                ],
                'bonuses.*' => [
                    'required_with:bonuses',
                    'array'
                ],
                'bonuses.*.id' => [
                    'nullable',
                    'int',
                    Rule::exists(Bonus::class, 'id')
                ],
                'bonuses.*.type' => [
                    'required_with:bonuses',
                    'string'
                ],
                'bonuses.*.price' => [
                    'required_with:bonuses',
                    'numeric'
                ],
                'bonuses.*.to' => [
                    'nullable',
                    'string',
                    Rule::in([
                        Payment::PAYER_BROKER,
                        Payment::PAYER_CUSTOMER,
                        Payment::PAYER_NONE
                    ])
                ],
                'tags' => [
                    'nullable',
                    'array',
                    'max:2'
                ],
                'tags.*' => [
                    'required_with:tags',
                    'int',
                    Rule::exists(Tag::class, 'id')
                        ->where('type', Tag::TYPE_ORDER)
                ]
            ],
            $this->getContactRules(self::PICKUP_CONTACT_KEY),
            $this->getContactRules(self::DELIVERY_CONTACT_KEY),
            $this->copyShipper ? [
                'shipper_copy_delivery' => [
                    'required',
                    'bool'
                ]
            ] : array_merge(
                [
                    self::SHIPPER_CONTACT_KEY . '_contact' => [
                        'required',
                        'array'
                    ]
                ],
                $this->getContactRules(self::SHIPPER_CONTACT_KEY)
            ),
            $this->getPaymentRules()
        );
    }

    /**
     * @param string $contactKey
     * @return array
     */
    private function getContactRules(string $contactKey): array
    {
        return array_merge(
            ContactRequest::getRules($contactKey . '_contact', $this->{'save' . ucfirst($contactKey) . 'Contact'}),
            [
                $contactKey . '_comment' => [
                    'nullable',
                    'string'
                ],
                $contactKey . '_save_contact' => [
                    'nullable',
                    'bool'
                ],
            ],
            $contactKey !== self::SHIPPER_CONTACT_KEY ? [
                $contactKey . '_date' => [
                    'nullable',
                    'date_format:' . DateTimeHelper::DATE_FORMAT
                ],
                $contactKey . '_time' => [
                    'nullable',
                    'array'
                ],
                $contactKey . '_time.from' => [
                    'nullable',
                    'date_format:' . DateTimeHelper::TIME_FORMAT
                ],
                $contactKey . '_time.to' => [
                    'nullable',
                    'date_format:' . DateTimeHelper::TIME_FORMAT
                ],
            ] : []
        );
    }

    private function getPaymentRules(): array
    {
        $payment = $this->input('payment');
        return [
            'payment.terms' => [
                'nullable',
                'string',
                'max:5000'
            ],
            'payment.invoice_notes' => [
                'nullable',
                'string',
                'max:5000'
            ],
            'payment.total_carrier_amount' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'payment.customer_payment_amount' => [
                'nullable',
                'numeric',
                Rule::requiredIf(static fn(): bool => ($payment['total_carrier_amount'] ?? 0) > (float)
                    $payment['broker_payment_amount'])
            ],
            'payment.customer_payment_method_id' => [
                'nullable',
                'int',
                Rule::requiredIf(static fn(): bool => (float)$payment['customer_payment_amount'] > 0),
                Rule::in(
                    array_keys(Payment::CUSTOMER_METHODS)
                )
            ],
            'payment.customer_payment_location' => [
                'nullable',
                Rule::requiredIf(static fn(): bool => (float)$payment['customer_payment_amount'] > 0),
                Rule::in(
                    array_keys(Order::LOCATIONS)
                )
            ],
            'payment.broker_payment_amount' => [
                'nullable',
                'numeric',
                Rule::requiredIf(
                    static fn() => ($payment['total_carrier_amount'] ?? 0) > (float)$payment['customer_payment_amount']
                )
            ],
            'payment.broker_payment_method_id' => [
                'nullable',
                'int',
                Rule::requiredIf(static fn(): bool => (float)$payment['broker_payment_amount'] > 0),
                Rule::in(
                    array_keys(Payment::BROKER_METHODS)
                )
            ],
            'payment.broker_payment_days' => [
                'nullable',
                Rule::requiredIf(static fn(): bool => (float)$payment['broker_payment_amount'] > 0),
                Rule::in(config('orders.payment.days'))
            ],
            'payment.broker_payment_begins' => [
                'nullable',
                Rule::requiredIf(static fn(): bool => (float)$payment['broker_payment_amount'] > 0),
                Rule::in(
                    array_keys(Order::TERMS_BEGINS)
                )
            ],
            'payment.broker_fee_amount' => [
                'nullable',
                'numeric',
                Rule::requiredIf(
                    static fn(
                    ): bool => (float)$payment['customer_payment_amount'] + (float)$payment['broker_payment_amount'] > ($payment['total_carrier_amount'] ?? 0)
                )
            ],
            'payment.broker_fee_method_id' => [
                'nullable',
                Rule::requiredIf(static fn(): bool => (float)$payment['broker_fee_amount'] > 0),
                Rule::in(
                    array_keys(Payment::CARRIER_METHODS)
                )
            ],
            'payment.broker_fee_days' => [
                'nullable',
                Rule::requiredIf(static fn(): bool => (float)$payment['broker_fee_amount'] > 0),
                Rule::in(config('orders.payment.days'))
            ],
            'payment.broker_fee_begins' => [
                'nullable',
                Rule::requiredIf(static fn(): bool => (float)$payment['broker_fee_amount'] > 0),
                Rule::in([
                    Order::LOCATION_DELIVERY,
                    Order::LOCATION_PICKUP
                ])
            ],
        ];
    }

    public function toDto(): OrderDto
    {
        return OrderDto::init($this->validated());
    }

    /**
     * @OA\Schema (
     *     schema="VehicleRequest",
     *     type="object",
     *     required={"make", "model", "type_id"},
     *     properties={
     *          @OA\Property (
     *               property="id",
     *               type="integer",
     *               nullable=true
     *          ),
     *          @OA\Property (
     *               property="inop",
     *               type="boolean",
     *               nullable=true
     *          ),
     *          @OA\Property (
     *               property="enclosed",
     *               type="boolean",
     *               nullable=true
     *          ),
     *          @OA\Property (
     *               property="vin",
     *               type="string",
     *               pattern="^[a-z0-9]{17}$",
     *               nullable=true
     *          ),
     *          @OA\Property (
     *               property="year",
     *               type="string",
     *               nullable=true,
     *               maxLength=4
     *          ),
     *          @OA\Property (
     *               property="make",
     *               type="string",
     *               nullable=false,
     *               maxLength=255
     *          ),
     *          @OA\Property (
     *               property="model",
     *               type="string",
     *               nullable=false,
     *               maxLength=255
     *          ),
     *          @OA\Property (
     *               property="type_id",
     *               type="integer",
     *               nullable=false
     *          ),
     *          @OA\Property (
     *               property="color",
     *               type="string",
     *               nullable=true,
     *               maxLength=255
     *          ),
     *          @OA\Property (
     *               property="license_plate",
     *               type="string",
     *               nullable=true,
     *               maxLength=255
     *          ),
     *          @OA\Property (
     *               property="odometer",
     *               type="string",
     *               format="numeric",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="stock_number",
     *               type="string",
     *               nullable=true,
     *               maxLength=255
     *          )
     *     }
     * )
     * @OA\Schema (
     *     schema="PaymentRequest",
     *     type="object",
     *     required={"total_carrier_amount"},
     *     properties={
     *          @OA\Property (
     *               property="terms",
     *               type="string",
     *               nullable=true,
     *               maxLength=5000,
     *          ),
     *          @OA\Property (
     *               property="invoice_notes",
     *               type="string",
     *               nullable=true,
     *               maxLength=5000,
     *          ),
     *          @OA\Property (
     *               property="total_carrier_amount",
     *               type="number",
     *               format="float",
     *               nullable=false,
     *               minimum=0,
     *          ),
     *          @OA\Property (
     *               property="customer_payment_amount",
     *               description="Required if 'total_carrier_amount' > 'broker_payment_amount'",
     *               type="number",
     *               format="float",
     *               nullable=true,
     *               minimum=0,
     *          ),
     *          @OA\Property (
     *               property="customer_payment_method_id",
     *               description="Required if 'customer_payment_amount'",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="customer_payment_location",
     *               description="Required if 'customer_payment_amount'",
     *               type="string",
     *               nullable=true,
     *               enum={"pcikup", "delivery"},
     *          ),
     *          @OA\Property (
     *               property="broker_payment_amount",
     *               description="Required if 'total_carrier_amount' > 'customer_payment_amount'",
     *               type="number",
     *               format="float",
     *               nullable=true,
     *               minimum=0,
     *          ),
     *          @OA\Property (
     *               property="broker_payment_method_id",
     *               description="Required if 'broker_payment_amount'",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="broker_payment_days",
     *               description="Required if 'broker_payment_amount'",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="broker_payment_begins",
     *               description="Required if 'broker_payment_amount'",
     *               type="string",
     *               nullable=true,
     *               enum={"pcikup", "delivery", "invoice-sent"},
     *          ),
     *          @OA\Property (
     *               property="broker_fee_amount",
     *               description="Required if 'total_carrier_amount' < 'customer_payment_amount' + 'broker_payment_amount'",
     *               type="number",
     *               format="float",
     *               nullable=true,
     *               minimum=0,
     *          ),
     *          @OA\Property (
     *               property="broker_fee_method_id",
     *               description="Required if 'broker_fee_amount'",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="broker_fee_days",
     *               description="Required if 'broker_fee_amount'",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="broker_fee_begins",
     *               description="Required if 'broker_fee_amount'",
     *               type="string",
     *               nullable=true,
     *               enum={"pcikup", "delivery"},
     *          ),
     *     }
     * )
     * @OA\Schema (
     *     schema="ExpenseRequest",
     *     type="object",
     *     required={"type_id", "price", "date"},
     *     properties={
     *          @OA\Property (
     *               property="id",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="type_id",
     *               type="integer",
     *               nullable=false,
     *          ),
     *          @OA\Property (
     *               property="price",
     *               type="number",
     *               format="float",
     *               nullable=false,
     *          ),
     *          @OA\Property (
     *               property="date",
     *               description="Format: m/d/Y",
     *               type="string",
     *               format="date",
     *               nullable=false,
     *          ),
     *          @OA\Property (
     *               property="receipt_file",
     *               type="string",
     *               format="binary",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="to",
     *               type="string",
     *               nullable=true,
     *               enum={"broker", "carrier", "customer"},
     *          ),
     *     }
     * )
     * @OA\Schema (
     *     schema="BounseRequest",
     *     type="object",
     *     required={"type", "price"},
     *     properties={
     *          @OA\Property (
     *               property="id",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="price",
     *               type="number",
     *               format="float",
     *               nullable=false,
     *          ),
     *          @OA\Property (
     *               property="type",
     *               type="string",
     *               nullable=false,
     *          ),
     *          @OA\Property (
     *               property="to",
     *               type="string",
     *               nullable=true,
     *               enum={"broker", "carrier", "customer"},
     *          ),
     *     }
     * )
     * @OA\Schema (
     *     schema="OrderRequest",
     *     type="object",
     *     required={"load_id", "inspection_type", "pickup_contact", "delivery_contact", "vehicles", "payment"},
     *     properties={
     *          @OA\Property (
     *               property="load_id",
     *               type="string",
     *               minLength=2,
     *               maxLength=255,
     *               nullable=false
     *          ),
     *          @OA\Property (
     *               property="dispatcher_id",
     *               description="Required with 'driver_id' and for picked_up status",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="driver_id",
     *               description="Required for picked_up status",
     *               type="integer",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="inspection_type",
     *               type="integer",
     *               nullable=false,
     *               enum={10, 20, 30, 40}
     *          ),
     *          @OA\Property (
     *               property="instructions",
     *               type="string",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="dispatch_instructions",
     *               type="string",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="pickup_save_contact",
     *               type="boolean",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="delivery_save_contact",
     *               type="boolean",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="save_shipper_contact",
     *               type="boolean",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="need_review",
     *               type="boolean",
     *               nullable=true,
     *          ),
     *          @OA\Property (
     *               property="attachment_files",
     *               type="array",
     *               nullable=true,
     *               items=@OA\Items(
     *                  type="string",
     *                  description="Available mimes: pdf,png,jpg,jpeg,jpe,doc,docx,txt,xls,xlsx",
     *                  format="binary",
     *               ),
     *          ),
     *          @OA\Property (
     *               property="pickup_contact",
     *               type="object",
     *               nullable=false,
     *               ref="#/components/schemas/ContactRequest"
     *          ),
     *          @OA\Property (
     *               property="delivery_contact",
     *               type="object",
     *               nullable=false,
     *               ref="#/components/schemas/ContactRequest"
     *          ),
     *          @OA\Property (
     *               property="shipper_copy_delivery",
     *               type="boolean",
     *               nullable=true
     *          ),
     *          @OA\Property (
     *               property="shipper_contact",
     *               description="Required if 'shipper_copy_delivery' is false",
     *               type="object",
     *               nullable=true,
     *               ref="#/components/schemas/ContactRequest"
     *          ),
     *          @OA\Property(
     *               property="pickup_date",
     *               description="Date in format: m/d/Y",
     *               type="string",
     *               format="date",
     *               nullable=true
     *          ),
     *          @OA\Property(
     *               property="pickup_buyer_name_number",
     *               type="string",
     *               nullable=true
     *          ),
     *          @OA\Property(
     *               property="pickup_time",
     *               type="object",
     *               nullable=true,
     *               properties={
     *                  @OA\Property (
     *                      property="from",
     *                      description="Time in format: g:i A",
     *                      type="string",
     *                      format="time",
     *                      nullable=true,
     *                  ),
     *                  @OA\Property (
     *                      property="to",
     *                      description="Time in format: g:i A",
     *                      type="string",
     *                      format="time",
     *                      nullable=true,
     *                  ),
     *               }
     *          ),
     *          @OA\Property(
     *               property="pickup_comment",
     *               type="string",
     *               nullable=true
     *          ),
     *          @OA\Property(
     *               property="delivery_date",
     *               description="Date in format: m/d/Y",
     *               type="string",
     *               format="date",
     *               nullable=true
     *          ),
     *          @OA\Property(
     *               property="delivery_time",
     *               type="object",
     *               nullable=true,
     *               properties={
     *                  @OA\Property (
     *                      property="from",
     *                      description="Time in format: g:i A",
     *                      type="string",
     *                      format="time",
     *                      nullable=true,
     *                  ),
     *                  @OA\Property (
     *                      property="to",
     *                      description="Time in format: g:i A",
     *                      type="string",
     *                      format="time",
     *                      nullable=true,
     *                  ),
     *               }
     *          ),
     *          @OA\Property(
     *               property="delivery_comment",
     *               type="string",
     *               nullable=true
     *          ),
     *          @OA\Property(
     *               property="shipper_comment",
     *               type="string",
     *               nullable=true
     *          ),
     *          @OA\Property(
     *               property="vehicles",
     *               type="array",
     *               nullable=false,
     *               items=@OA\Items(type="object", ref="#/components/schemas/VehicleRequest"),
     *          ),
     *          @OA\Property(
     *               property="payment",
     *               type="object",
     *               nullable=false,
     *               ref="#/components/schemas/PaymentRequest"
     *          ),
     *          @OA\Property(
     *               property="expenses",
     *               type="array",
     *               nullable=true,
     *               items=@OA\Items(type="object", ref="#/components/schemas/ExpenseRequest"),
     *          ),
     *          @OA\Property(
     *               property="bonuses",
     *               type="array",
     *               nullable=true,
     *               items=@OA\Items(type="object", ref="#/components/schemas/BounseRequest"),
     *          ),
     *          @OA\Property(
     *               property="tags",
     *               type="array",
     *               nullable=true,
     *               items=@OA\Items(type="integer"),
     *          ),
     *     }
     * )
     */
}
