<?php

namespace WezomCms\Orders\Http\Livewire;

use Auth;
use DB;
use Hash;
use Illuminate\Support\Arr;
use Lang;
use Livewire\Component;
use Throwable;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Core\Rules\PhoneMask;
use WezomCms\Core\Services\CheckForSpam;
use WezomCms\Orders\Cart\Adapters\CheckoutAdapter;
use WezomCms\Orders\Contracts\CartInterface;
use WezomCms\Orders\Contracts\CartItemInterface;
use WezomCms\Orders\Contracts\DeliveryDriverInterface;
use WezomCms\Orders\Events\CreatedOrder;
use WezomCms\Orders\Models\Delivery;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderDeliveryInformation;
use WezomCms\Orders\Models\OrderItem;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Models\Payment;
use WezomCms\Orders\Traits\CourierCheckoutTrait;
use WezomCms\Users\Events\AutoRegistered;
use WezomCms\Users\Models\User;

/**
 * Class Checkout
 * @package WezomCms\Orders\Http\Livewire
 * @property-read $allDeliveries
 * @property Delivery|null $delivery
 * @property-read $groupedPayments
 */
class Checkout extends Component
{
    use CourierCheckoutTrait;

    /**
     * User information.
     *
     * @var null[]
     */
    public $user = [
        'name' => null,
        'surname' => null,
        'patronymic' => null,
        'phone' => null,
        'email' => null,
        'registerMe' => null,
    ];

    /**
     * If order recipient is me.
     *
     * @var bool
     */
    public $recipientIsMe = true;

    /**
     * Recipient information.
     *
     * @var array
     */
    public $recipient = [
        'name' => null,
        'surname' => null,
        'patronymic' => null,
        'phone' => null,
        'comment' => null,
    ];

    /**
     * @var bool
     */
    public $showCommentField = false;

    /**
     * @var int|null
     */
    public $deliveryId;

    /**
     * Array for storing delivery driver fields.
     *
     * @var array
     */
    public $deliveryData = [];

    /**
     * @var bool
     */
    public $dontCallBack = false;

    /**
     * @var int|null
     */
    public $paymentId;

    /**
     * @var string[]
     */
    protected $listeners = ['cartUpdated' => '$refresh', '$refresh'];

    public function mount()
    {
        /** @var User $user */
        $user = optional(Auth::user());

        $this->user['name'] = $user->name;
        $this->user['surname'] = $user->surname;
        $this->user['patronymic'] = $user->patronymic;
        $this->user['phone'] = $user->masked_phone;
        $this->user['email'] = $user->email;
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function render()
    {
        $cart = app(CartInterface::class);

        if ($cart->isEmpty() && null === $this->redirectTo) {
            $this->redirectRoute('checkout');
            return '';
        }

        if ($last = $cart->content()->last()) {
            $backUrl = $last->getPurchaseItem()->getFrontUrl();
        } else {
            $backUrl = route('catalog');
        }

        $deliveries = Delivery::published()
            ->sorting()
            ->get();

        $payments = Payment::published()
            ->sorting()
            ->get();

        return view('cms-orders::site.livewire.checkout', [
            'backUrl' => $backUrl,
            'cart' => (new CheckoutAdapter($cart))->adapt(),
            'deliveries' => $deliveries,
            'delivery' => $this->delivery,
            'groupedDeliveryPayments' => $this->groupedPaymentDelivery(),
            'payments' => $payments,
            'hasUnavailableProducts' => $cart->content()->filter(function (CartItemInterface $cartItem) {
                return !$cartItem->getPurchaseItem()->availableForPurchase();
            })->isNotEmpty(),
        ]);
    }

    /**
     * Form submit handler
     * @param  CheckForSpam  $checkForSpam
     * @param  CartInterface  $cart
     */
    public function submit(CheckForSpam $checkForSpam, CartInterface $cart)
    {
        if (!$checkForSpam->checkInComponent($this, $this->user['email'])) {
            return;
        }

        if ($cart->isEmpty()) {
            return;
        }

        $validatedData = $this->validate(...$this->rules());

        if (Auth::guest() && $this->user['registerMe'] && $this->tryRegisterUser() !== true) {
            return;
        }

        if ($order = $this->createOrder($validatedData)) {
            session()->put('order-id', $order->id);

            // Redirect to payment link or thanks page
            if ($paymentUrl = $order->getOnlinePaymentUrl()) {
                $this->redirect($paymentUrl);

                return;
            }

            $this->redirect(route('thanks-page', $order->id));
        }
    }

    /**
     * Validate only updated field.
     *
     * @param $field
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updated($field)
    {
        $this->validateOnly($field, ...$this->rules());
    }

    /**
     * @param $value
     */
    public function updatedRecipientIsMe($value)
    {
        $this->recipientIsMe = (bool)$value;
    }

    protected function tryRegisterUser()
    {
        $userInfo = $this->user;
        $userInfo['phone'] = remove_phone_mask($userInfo['phone']);

        // Validate email && phone
        $validator = \Validator::make(
            $userInfo,
            [
                'email' => 'unique:users',
                'phone' => 'unique:users',
            ],
            [
                'email.unique' => __('cms-orders::site.checkout.User with provided email already exists'),
                'phone.unique' => __('cms-orders::site.checkout.User with provided phone already exists'),
            ],
            [
                'email' => __('cms-orders::site.checkout.Email'),
                'phone' => __('cms-orders::site.checkout.Phone'),
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->messages()->messages() as $field => $messages) {
                $this->addError("user.$field", Arr::first($messages));
            }
            return false;
        }

        $passwordMinLength = config('cms.users.users.password_min_length');
        $password = mt_rand('1' . str_repeat(0, $passwordMinLength - 1), str_repeat('9', $passwordMinLength));
        event(new AutoRegistered($user = $this->createUser($userInfo, $password), $password));

        Auth::guard()->login($user);

        return true;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @param $password
     * @return User
     */
    protected function createUser(array $data, $password)
    {
        $user = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'patronymic' => $data['patronymic'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'registered_through' => User::emailOrPhone($data['email']),
            'active' => true,
            'password' => Hash::make($password),
        ]);

        $user->markEmailAsVerified();

        return $user;
    }

    /**
     * @param  int  $deliveryId
     */
    public function updatingDeliveryId(int $deliveryId)
    {
        $allDeliveries = $this->allDeliveries;

        /** @var Delivery $deliveryVariant */
        if ($deliveryVariant = $allDeliveries->get($deliveryId)) {
            /** @var DeliveryDriverInterface|null $driver */
            if ($driver = $deliveryVariant->makeDriver()) {
                $this->deliveryData = $driver->getFormInputs();
            } else {
                $this->deliveryData = array_fill_keys(['city', 'street', 'house', 'room'], '');
            }
        }
    }

    /**
     * @return array
     */
    protected function rules(): array
    {
        $rules = [
            // User
            'user.name' => 'required|string|max:255',
            'user.surname' => 'required|string|max:255',
            'user.patronymic' => 'nullable|string|max:255',
            'user.phone' => ['required', 'string', new PhoneMask()],
            'user.email' => 'required|string|email|max:255',
            'user.registerMe' => 'nullable|bool',

            // Recipient
            'recipient.name' => 'bail|nullable|required_if:recipientIsMe,0|string|max:255',
            'recipient.surname' => 'bail|nullable|required_if:recipientIsMe,0|string|max:255',
            'recipient.patronymic' => 'bail|nullable|string|max:255',
            'recipient.phone' => ['bail', 'nullable', 'required_if:recipientIsMe,0', 'string', new PhoneMask()],
            'recipient.comment' => 'nullable|string|max:700',

            'deliveryId' => 'required|exists:deliveries,id,published,1',
            'paymentId' => 'required|exists:payments,id,published,1',
        ];

        $messages = [
            'recipient.name.required_if' => Lang::get('validation.required'),
            'recipient.surname.required_if' => Lang::get('validation.required'),
            'recipient.phone.required_if' => Lang::get('validation.required'),
        ];

        $attributes = [
            // User
            'user.name' => __('cms-orders::site.checkout.Name'),
            'user.surname' => __('cms-orders::site.checkout.Surname'),
            'user.patronymic' => __('cms-orders::site.checkout.Patronymic'),
            'user.phone' => __('cms-orders::site.checkout.Phone'),
            'user.email' => __('cms-orders::site.checkout.E-mail'),
            'user.registerMe' => __('cms-orders::site.checkout.Register me'),

            // Recipient
            'recipient.name' => __('cms-orders::site.checkout.Name'),
            'recipient.surname' => __('cms-orders::site.checkout.Surname'),
            'recipient.patronymic' => __('cms-orders::site.checkout.Patronymic'),
            'recipient.phone' => __('cms-orders::site.checkout.Phone'),
            'recipient.comment' => __('cms-orders::site.checkout.Comment'),

            'deliveryId' => __('cms-orders::site.checkout.Delivery'),
            'paymentId' => __('cms-orders::site.checkout.Payment'),
        ];

        // Add delivery rules
        $delivery = Delivery::published()->find($this->deliveryId);
        if ($delivery && $driver = $delivery->makeDriver($this->only(['deliveryData']))) {
            [$dataRules, $dataMessages, $dataAttributes] = $driver->getValidationRules();

            $rules = array_merge($rules, $this->addPrefixToKeys('deliveryData', $dataRules));
            $messages = array_merge($messages, $this->addPrefixToKeys('deliveryData', $dataMessages));
            $attributes = array_merge($attributes, $this->addPrefixToKeys('deliveryData', $dataAttributes));
        }

        return [$rules, $messages, $attributes];
    }

    /**
     * @param  array  $validatedData
     * @return Order
     */
    protected function createOrder(array $validatedData): ?Order
    {
        $cart = app(CartInterface::class);

        try {
            $order = DB::transaction(function () use ($cart, $validatedData) {
                $order = Order::create();
                $order->dont_call_back = $this->dontCallBack;

                // Store delivery
                if ($delivery = Delivery::published()->find($this->deliveryId)) {
                    $order->delivery()->associate($delivery);

                    /** @var OrderDeliveryInformation $deliveryInformation */
                    $deliveryInformation = $order->deliveryInformation()->make();

                    if ($driver = $delivery->makeDriver($this->only(['deliveryData']))) {
                        $driver->fillStorage($deliveryInformation, $this->deliveryData);
                    }

                    $deliveryInformation->save();
                }

                // Store payment
                if ($payment = Payment::published()->find($this->paymentId)) {
                    $order->payment()->associate($payment);
                }

                // User
                if ($user = Auth::user()) {
                    $order->user()->associate($user);
                }

                $order->client()->create([
                    'name' => $this->user['name'],
                    'surname' => $this->user['surname'],
                    'patronymic' => $this->user['patronymic'],
                    'email' => $this->user['email'],
                    'phone' => remove_phone_mask($this->user['phone']),
                ]);

                // Recipient
                $recipientData = [
                    'recipient_is_me' => $this->recipientIsMe,
                    'comment' => $this->showCommentField ? strip_tags($this->recipient['comment']) : null,
                ];

                if (!$this->recipientIsMe) {
                    $recipientData['name'] = $this->recipient['name'];
                    $recipientData['surname'] = $this->recipient['surname'];
                    $recipientData['patronymic'] = $this->recipient['patronymic'];
                    $recipientData['phone'] = remove_phone_mask($this->recipient['phone']);
                }

                $order->recipient()->create($recipientData);

                // Set order status as new.
                $order->changeStatus(OrderStatus::newStatus());

                // Save order
                $order->save();

                // Save cart content
                foreach ($cart->content() as $cartItem) {
                    $product = $cartItem->getPurchaseItem();

                    /** @var OrderItem $orderItem */
                    $orderItem = $order->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $cartItem->getQuantity(),
                        'price' => $cartItem->getPurchaseItem()->oldPriceForPurchase(),
                        'purchase_price' => $cartItem->getTotal(),
                    ]);

                    /** @var Product|null $gift */
                    if ($gift = $product->active_gift) {
                        $orderItem->gift()->associate($gift);
                    }

                    $orderItem->save();
                }

                if (method_exists($this, 'afterCreationOrder')) {
                    call_user_func([$this, 'afterCreationOrder'], $order);
                }

                $order->save();

                $order->fresh();

                event(new CreatedOrder($order));

                return $order;
            }, 3);

            // Clear cart
            $cart->clear();

            $this->reset();

            return $order;
        } catch (Throwable $e) {
            logger('Order creation error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            JsResponse::make()
                ->notification(
                    __('cms-orders::site.checkout.Order creation error Please try later'),
                    'error',
                    20
                )->emit($this);

            $this->emitSelf('$refresh');

            return null;
        }
    }

    /**
     * @return Delivery|null
     */
    public function getDeliveryProperty(): ?Delivery
    {
        return $this->deliveryId ? Delivery::published()->find($this->deliveryId) : null;
    }

    /**
     * @return \Illuminate\Support\Collection|Delivery[]
     */
    public function getAllDeliveriesProperty()
    {
        return Delivery::published()
            ->with([
                'payments' => function ($query) {
                    $query->published()->select('id');
                }
            ])
            ->whereHas('payments', published_scope())
            ->orderBy('sort')
            ->latest('id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item];
            });
    }

    /**
     * @return \Illuminate\Support\Collection|Payment[]
     */
    public function groupedPaymentDelivery()
    {
        return Payment::published()
            ->with([
                'deliveries' => function ($query) {
                    $query->published()->select('id');
                }
            ])
            ->orderBy('sort')
            ->latest('id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->deliveries];
            });
    }

    /**
     * @param  string  $prefix
     * @param  array  $items
     * @return array
     */
    protected function addPrefixToKeys(string $prefix, array $items): array
    {
        $result = [];
        foreach ($items as $key => $value) {
            $result["$prefix.$key"] = $value;
        }
        return $result;
    }
}
