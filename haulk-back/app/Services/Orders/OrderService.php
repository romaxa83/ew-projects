<?php

namespace App\Services\Orders;

use App\Documents\Filters\Exceptions\DocumentFilterMethodNotFoundException;
use App\Documents\OrderDocument;
use App\Dto\Images\TextLine;
use App\Dto\Orders\BonusDto;
use App\Dto\Orders\ExpenseDto;
use App\Dto\Orders\InspectDamageDto;
use App\Dto\Orders\InspectExteriorDto;
use App\Dto\Orders\OrderDto;
use App\Dto\Orders\OrderIndexDto;
use App\Dto\Orders\VehicleDto;
use App\Events\ModelChanged;
use App\Exceptions\Order\EmptyInvoiceTotalDue;
use App\Exceptions\Order\HaveToAgreeWithInspection;
use App\Exceptions\Order\NotVinInspectionException;
use App\Exceptions\Order\OrderAlreadySigned;
use App\Exceptions\Order\OrderCantBeMovedOffers;
use App\Exceptions\Order\OrderHasNotHadInspectionYet;
use App\Exceptions\Order\OrderSignatureLinkExpired;
use App\Exports\OrderExport;
use App\Models\Contacts\Contact;
use App\Models\History\History;
use App\Models\Orders\Bonus;
use App\Models\Orders\Expense;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use App\Models\Orders\PaymentStage;
use App\Models\Orders\Vehicle;
use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Users\User;
use App\Notifications\Alerts\AlertNotification;
use App\Notifications\Orders\SendSignatureLink;
use App\Services\Contacts\ContactService;
use App\Services\Events\EventService;
use App\Services\Events\Order\OrderEventService;
use App\Services\Google\Commands\Map\GetDistanceBetweenAddresses;
use App\Services\Images\DrawingImageInterface;
use App\Services\Push\PushService;
use DB;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\MediaCannotBeDeleted;
use Spatie\MediaLibrary\Models\Media;
use Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OrderService
{
    private ContactService $contactService;

    private DrawingImageInterface $imageService;

    private GeneratePdfService $generatePdfService;

    private ?User $user = null;

    public function __construct(
        ContactService $contactService,
        DrawingImageInterface $imageService,
        GeneratePdfService $generatePdfService
    ) {
        $this->contactService = $contactService;
        $this->imageService = $imageService;
        $this->generatePdfService = $generatePdfService;
    }

    public function setUser(?User $user): OrderService
    {
        $this->user = $user;

        $this->contactService->setUser($user);

        return $this;
    }

    public function vehiclesForFilter(): array
    {
        $make = Vehicle::query()->select(['make', 'model', 'year'])->get();

        $data = [];
        $result = [];

        if (count($make)) {
            foreach ($make as $m) {
                if (isset($data[$m['make']])) {
                    if (isset($data[$m['make']][$m['model']])) {
                        if (!in_array($m['year'], $data[$m['make']][$m['model']])) {
                            $data[$m['make']][$m['model']][] = $m['year'];
                        }
                    } else {
                        $data[$m['make']][$m['model']] = [
                            $m['year']
                        ];
                    }
                } else {
                    $data[$m['make']] = [
                        $m['model'] => [
                            $m['year']
                        ]
                    ];
                }
            }
        }

        if ($data) {
            foreach ($data as $make => $model_year) {
                $make_children = [];

                foreach ($model_year as $model => $years) {
                    $model_children = [];

                    foreach ($years as $year) {
                        $model_children[] = [
                            'value' => $year,
                            'label' => $year,
                        ];
                    }

                    $make_children[] = [
                        'value' => $model,
                        'label' => $model,
                        'children' => $model_children
                    ];
                }

                $result[] = [
                    'value' => $make,
                    'label' => $make,
                    'children' => $make_children
                ];
            }
        }

        return $result;
    }

    /**
     * @param string|null $status
     * @param int $perPage
     * @param int $page
     * @return LengthAwarePaginator
     * @throws DocumentFilterMethodNotFoundException
     */
    public function getMobileOrderList(?string $status, int $perPage, int $page): LengthAwarePaginator
    {
        $filter = [
            'without_deleted' => true,
        ];
        if ($status) {
            $filter['mobile_tab'] = $status;
            switch ($status) {
                case Order::MOBILE_TAB_IN_WORK:
                    $sort = [
                        OrderDocument::status() => 'desc',
                        OrderDocument::deliveryPlannedDate() => 'asc',
                        OrderDocument::id() => 'asc'
                    ];
                    break;
                case Order::MOBILE_TAB_PLAN:
                    $sort = [
                        OrderDocument::pickupPlannedDate() => 'asc',
                        OrderDocument::id() => 'asc'
                    ];
                    break;
                case Order::MOBILE_TAB_HISTORY:
                    $sort = [
                        OrderDocument::deliveryDateActual() => request()->get('order_by') === 'asc' ? 'asc' : 'desc',
                    ];
                    break;
                default:
                    $sort = [
                        OrderDocument::id() => 'asc',
                    ];
                    break;
            }
        }
        return resolve(OrderSearchService::class)
            ->paginate(
                $page,
                $perPage,
                $filter,
                $sort ?? null,
                true
            );
    }

    /**
     * @param OrderIndexDto $dto
     * @return LengthAwarePaginator
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function getOrderList(OrderIndexDto $dto): LengthAwarePaginator
    {
        /** @var $searchService OrderSearchService */
        $searchService = resolve(OrderSearchService::class);

        return $searchService->paginate(
            $dto->getPage(),
            $dto->getPerPage(),
            $dto->getFilter(),
            !empty($dto->getOrderBy()) ? [
                $dto->getOrderBy() => $dto->getOrderType(),
            ] : null
        );
    }

    /**
     * @param OrderDto $dto
     * @return Order
     * @throws Throwable
     */
    public function create(OrderDto $dto): Order
    {
        DB::beginTransaction();

        $order = $this->fillOrder(
            Order::make()
                ->setStatus(Order::STATUS_NEW)
                ->setUser($this->user)
                ->setPublicToken(),
            $dto
        );

        EventService::order($order)
            ->user($this->user)
            ->create()
            ->broadcast()
            ->push();

        DB::commit();

        return $order;
    }

    /**
     * @param Order $order
     * @param OrderDto $dto
     * @return Order
     * @throws Throwable
     */
    public function update(Order $order, OrderDto $dto): Order
    {
        $order->load('payment');

        $event = EventService::order($order);
        $order = $this->fillOrder($order, $dto);

        $event->user($this->user)
            ->update()
            ->push()
            ->broadcast();

        return $order;
    }

    /**
     * @param Order $order
     * @param OrderDto $dto
     * @return Order
     * @throws Throwable
     */
    private function fillOrder(Order $order, OrderDto $dto): Order
    {
        try {
            DB::beginTransaction();
            $order
                ->setLoadId($dto->loadId)
                ->setDispatcherId($dto->dispatcherId)
                ->setDriverId($dto->driverId)
                ->setInstructions($dto->instructions)
                ->setDispatchInstructions($dto->dispatchInstructions)
                ->setInspectionType($dto->inspectionType)
                ->setNeedReview($dto->needReview)
                ->setPickupDate($dto->pickupDate)
                ->setPickupTime($dto->pickupTime)
                ->setPickupComment($dto->pickupComment)
                ->setPickupBuyerNameNumber($dto->pickupBuyerNameNumber)
                ->setDeliveryDate($dto->deliveryDate)
                ->setDeliveryTime($dto->deliveryTime)
                ->setDeliveryComment($dto->deliveryComment)
                ->setShipperComment($dto->shipperComment)
                ->setPickupContact($dto->pickupContact)
                ->setDeliveryContact($dto->deliveryContact)
                ->setShipperContact($dto->shipperContact);

            if ($dto->pickupSaveContact && $dto->pickupContact->typeId !== Contact::CONTACT_TYPE_PRIVATE) {
                $this->contactService->create($dto->pickupContact);
            }
            if ($dto->deliverySaveContact && $dto->deliveryContact->typeId !== Contact::CONTACT_TYPE_PRIVATE) {
                $this->contactService->create($dto->deliveryContact);
            }
            if ($dto->shipperSaveContact && $dto->shipperContact->typeId !== Contact::CONTACT_TYPE_PRIVATE && !$dto->shipperCopyDelivery) {
                $this->contactService->create($dto->shipperContact);
            }
            if ($order->isDirty('driver_id')) {
                $order->seen_by_driver = false;
            }

            if(
                empty($order->distance_data)
                && !empty($order->pickup_contact)
                && !empty($order->delivery_contact)
            ){
                $this->paymentForDistance($order);
            }

            $order->saveOrFail();
            if ($order->wasRecentlyCreated) {
                $payment = $order->payment()->make($dto->payment->toArray());
            } else {
                $order->payment->fill($dto->payment->toArray());
                $payment = $order->payment;
            }
            OrderPaymentService::init()->updatePlannedDate($payment);
            $this->fillVehicles($order, $dto->vehicles);
            if ($dto->expenses !== null) {
                $dto
                    ->expenses
                    ->each(
                        static function (ExpenseDto $expenseDto) use ($order): void {
                            /**@var Expense $expense */
                            $expense = $order->expenses()->updateOrCreate(
                                [
                                    'id' => $expenseDto->id ?? 0
                                ],
                                $expenseDto->toArray()
                            );
                            if ($expenseDto->receiptFile === null) {
                                return;
                            }
                            $expense->addMediaWithRandomName(
                                Expense::EXPENSE_COLLECTION_NAME,
                                $expenseDto->receiptFile,
                                true
                            );
                        }
                    );
            } else {
                $order->expenses()->delete();
            }

            if ($dto->bonuses !== null) {
                $dto
                    ->bonuses
                    ->each(
                        static fn(BonusDto $bonusDto) => $order
                            ->bonuses()
                            ->updateOrCreate(
                                [
                                    'id' => $bonusDto->id ?? 0,
                                ],
                                $bonusDto->toArray()
                            )
                    );
            } else {
                $order->bonuses()->delete();
            }
            if ($dto->attachments !== null) {
                $dto
                    ->attachments
                    ->each(
                        static fn(UploadedFile $attachment) => $order
                            ->addMediaWithRandomName(Order::ATTACHMENT_COLLECTION_NAME, $attachment)
                    );
            }
            $order->tags()->sync($dto->tags ?? []);
            DB::commit();
            return $order;
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Collection<VehicleDto> $vehicles
     * @return void
     */
    private function fillVehicles(Order $order, Collection $vehicles): void
    {
        $ids = $vehicles
            ->filter(static fn(VehicleDto $vehicleDto) => $vehicleDto->id !== null)
            ->pluck('id')
            ->values()
            ->toArray();

        if (empty($ids)) {
            $order->vehicles()->delete();
        } else {
            $order->vehicles()->whereNotIn('id', $ids)->delete();
        }

        $vehicles
            ->each(
                static fn(VehicleDto $vehicleDto) => $order
                    ->vehicles()
                    ->updateOrCreate(
                        [
                            'id' => $vehicleDto->id ?? 0
                        ],
                        $vehicleDto->toArray()
                    )
            );
    }

    protected function getPushService(): PushService
    {
        return resolve(PushService::class);
    }

    /**
     * @param Order $order
     * @throws Throwable
     */
    public function markSeenByDriver(Order $order): void
    {
        $order->seen_by_driver = true;
        $order->saveOrFail();

        EventService::order($order)
            ->user($this->user)
            ->update()
            ->broadcast();
    }

    /**
     * @param Order $order
     * @return Order
     * @throws Throwable
     */
    public function markReviewed(Order $order): Order
    {
        if ($order->need_review) {
            $event = EventService::order($order);

            $order->has_review = true;
            $order->saveOrFail();

            $event->user($this->user)
                ->update()
                ->broadcast();
        }

        return $order;
    }

    /**
     * @param Order $order
     * @param array $assignDriverData
     * @return Order
     * @throws Throwable
     */
    public function assignDriver(Order $order, array $assignDriverData): Order
    {
        $event = EventService::order($order);

        $order->fill($assignDriverData);

        if ($order->isDirty('driver_id')) {
            $order->seen_by_driver = false;
        }

        $order->saveOrFail();

        $event->user($this->user)
            ->update()
            ->push()
            ->broadcast();

        return $order;
    }

    /**
     * @param Order $order
     */
    public function deleteOrder(Order $order): void
    {
        $order->delete();

        EventService::order($order)
            ->user($this->user)
            ->delete()
            ->push()
            ->broadcast();
    }

    public function restoreOrder(Order $order): Order
    {
        $order->restore();

        EventService::order($order)
            ->user($this->user)
            ->restore()
            ->push()
            ->broadcast();

        return $order;
    }

    /**
     * @param Order $order
     * @return void
     * @throws Throwable
     */
    public function deleteOrderPermanently(Order $order): void
    {
        try {
            DB::beginTransaction();

            $order->forceDelete();

            EventService::order($order)
                ->user($this->user)
                ->delete()
                ->push()
                ->broadcast();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param array $requestData
     * @return Order
     * @throws Throwable
     */
    public function addVehicle(Order $order, array $requestData): Order
    {
        try {
            DB::beginTransaction();
            $event = EventService::order($order);

            $order->vehicles()->create($requestData);
            $order->saveOrFail();

            DB::commit();

            $event->user($this->user)
                ->update(OrderEventService::ACTION_ADD_VEHICLE)
                ->push()
                ->broadcast();

            return $order;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param array $requestData
     * @return Order
     * @throws Throwable
     */
    public function editVehicle(Order $order, Vehicle $vehicle, array $requestData): Order
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }
        $event = EventService::order($order);

        $vehicle->fill($requestData);
        $vehicle->saveOrFail();

        $event->user($this->user)
            ->update()
            ->push()
            ->broadcast();

        return $order;
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @return void
     * @throws Throwable
     */
    public function deleteVehicle(Order $order, Vehicle $vehicle): void
    {
        try {
            DB::beginTransaction();
            $event = EventService::order($order);

            if ($vehicle->deliveryInspection) {
                $vehicle->deliveryInspection->delete();
                $vehicle->delivery_inspection_id = null;
            }

            if ($vehicle->pickupInspection) {
                $vehicle->pickupInspection->delete();
                $vehicle->pickup_inspection_id = null;
            }

            $vehicle->delete();

            $event->user($this->user)
                ->update(OrderEventService::ACTION_DELETE_VEHICLE)
                ->push()
                ->broadcast();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error($e->getTraceAsString());

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param array $expenseData
     * @return Order
     * @throws Throwable
     */
    public function addExpense(Order $order, array $expenseData): Order
    {
        try {
            DB::beginTransaction();
            $event = EventService::order($order);

            $expenseData['date'] = isset($expenseData['date']) ? strtotime($expenseData['date']) : null;
            /** @var Expense $expenseModel */
            $expenseModel = $order->expenses()->create($expenseData);

            if (isset($expenseData[Expense::RECEIPT_FIELD_NAME])) {
                $expenseModel->addMediaWithRandomName(
                    Expense::EXPENSE_COLLECTION_NAME,
                    $expenseData[Expense::RECEIPT_FIELD_NAME]
                );
            }

            $order->saveOrFail();

            $event->user($this->user)
                ->update()
                ->push()
                ->broadcast();

            DB::commit();

            return $order;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Expense $expense
     * @param array $expenseData
     * @return Order
     * @throws Exception
     */
    public function editExpense(Order $order, Expense $expense, array $expenseData): Order
    {
        if ($expense->order_id !== $order->id) {
            throw new Exception(trans('Expense not found.'));
        }

        try {
            DB::beginTransaction();
            $event = EventService::order($order);

            $expenseData['date'] = isset($expenseData['date']) ? strtotime($expenseData['date']) : null;

            $expense->fill($expenseData);
            $expense->save();

            if (isset($expenseData[Expense::RECEIPT_FIELD_NAME])) {
                $expense->addMediaWithRandomName(
                    Expense::EXPENSE_COLLECTION_NAME,
                    $expenseData[Expense::RECEIPT_FIELD_NAME],
                    true
                );
            }

            $event->user($this->user)
                ->update()
                ->push()
                ->broadcast();

            DB::commit();

            return $order;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Expense $expense
     * @throws Exception
     */
    public function deleteExpense(Order $order, Expense $expense): void
    {
        if ($expense->order_id !== $order->id) {
            throw new Exception(trans('Expense not found.'));
        }
        $event = EventService::order($order);

        $expense->clearMediaCollection(Expense::EXPENSE_COLLECTION_NAME);
        $expense->delete();

        $event->user($this->user)
            ->update(OrderEventService::ACTION_DELETE_EXPENSE)
            ->push()
            ->broadcast();
    }

    /**
     * @param Order $order
     * @param Bonus $bonus
     * @throws Exception
     */
    public function deleteBonus(Order $order, Bonus $bonus): void
    {
        if ($bonus->order_id !== $order->id) {
            throw new Exception(trans('Expense not found.'));
        }
        $event = EventService::order($order);

        $bonus->delete();

        $order->refresh();

        $event->user($this->user)
            ->update(OrderEventService::ACTION_DELETE_BONUS)
            ->push()
            ->broadcast();
    }

    /**
     * @param Order $order
     * @param null $attachment
     * @return Order
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addAttachment(Order $order, $attachment = null): Order
    {
        if ($this->user && $attachment) {
            try {
                $event = EventService::order($order);

                $order->addMediaWithRandomName(Order::ATTACHMENT_COLLECTION_NAME, $attachment);

                $event->user($this->user)
                    ->update()
                    ->push()
                    ->broadcast();
            } catch (Throwable $e) {
                throw $e;
            }
        }

        return $order;
    }

    /**
     * @param Order $order
     * @param int $mediaId
     * @throws MediaCannotBeDeleted
     * @throws Exception
     */
    public function deleteAttachment(Order $order, int $mediaId = 0): void
    {
        if ($this->user && $order->media->find($mediaId)) {
            $event = EventService::order($order);

            $order->deleteMedia($mediaId);

            $event->user($this->user)
                ->update(OrderEventService::ACTION_DELETE_ATTACHMENT)
                ->push()
                ->broadcast();

            return;
        }

        throw new Exception(trans('File not found.'));
    }

    /**
     * @param Order $order
     * @return Order
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Exception
     */
    public function duplicateOrder(Order $order): Order
    {
        try {
            DB::beginTransaction();

            $replica = Order::find($order->id)
                ->replicate();

            // ??? $replica->status = 10;
            $replica->user_id = Auth::user()->id;
            $replica->load_id = $order->load_id . ' duplicate';
            $replica->public_token = hash('sha256', Str::random(60));

            // unset replica autogenerated fields
            unset(
                $replica->pickup_full_name,
                $replica->delivery_full_name,
                $replica->shipper_full_name,
                $replica->driver_delivery_id,
                $replica->driver_pickup_id,
            );

            $replica->save();

            // add vehicles
            if ($order->vehicles->count()) {
                foreach ($order->vehicles as $vehicle) {
                    $vc = $vehicle->replicate();
                    $vc->pickup_inspection_id = null;
                    $vc->delivery_inspection_id = null;
                    $replica->vehicles()->save($vc);
                }
            }

            // add payment
            $pc = $order->payment->replicate();
            $replica->payment()->save($pc);

            // add payment stages
            if ($order->paymentStages->count()) {
                foreach ($order->paymentStages as $stage) {
                    $sc = $stage->replicate();
                    $replica->paymentStages()->save($sc);
                }
            }

            // add expenses
            if ($order->expenses->count()) {
                foreach ($order->expenses as $expense) {
                    $ec = $expense->replicate();
                    $ec->save();

                    if ($expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)) {
                        $ec->clearMediaCollection(Expense::EXPENSE_COLLECTION_NAME);
                        $ec->addMediaFromDisk($expense->getFirstMediaPath(Expense::EXPENSE_COLLECTION_NAME))
                            ->preservingOriginal()
                            ->usingName($expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)->name)
                            ->setFileName(
                                media_hash_file(
                                    $expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)->getUrl(),
                                    $expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)->getExtensionAttribute()
                                )
                            )->toMediaCollection(Expense::EXPENSE_COLLECTION_NAME);
                    }

                    $replica->expenses()->save($ec);
                }
            }

            // add attachments
            if ($order->getMedia(Order::ATTACHMENT_COLLECTION_NAME)) {
                $replica->clearMediaCollection(Order::ATTACHMENT_COLLECTION_NAME);

                foreach ($order->getMedia(Order::ATTACHMENT_COLLECTION_NAME)->all() as $attachment) {
                    $replica->addMediaFromDisk($attachment->getPath())
                        ->preservingOriginal()
                        ->usingName($attachment->name)
                        ->setFileName(
                            media_hash_file(
                                $attachment->getUrl(),
                                $attachment->getExtensionAttribute()
                            )
                        )->toMediaCollection(Order::ATTACHMENT_COLLECTION_NAME);
                }
            }

            if ($order->tags()->count()) {
                $tags = [];
                foreach ($order->tags as $tag) {
                    $tags[] = $tag->id;
                }
                $replica->tags()->sync($tags);
            }

            $replica->save();

            EventService::order($replica)
                ->user($this->user)
                ->duplicated($order->load_id)
                ->push()
                ->broadcast();

            DB::commit();

            return $replica->refresh();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param User $user
     * @param array $splitRequestData
     * @return void
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    public function splitOrder(Order $order, User $user, array $splitRequestData): void
    {
        try {
            DB::beginTransaction();

            $event = EventService::order($order);

            $order = Order::withoutGlobalScopes()
                ->find($order->id);

            $order->load_id = $splitRequestData['source_load_id'];
            $order->saveOrFail();

            foreach ($splitRequestData['destination'] as $destination) {
                $replica = $order->replicate();
                $replica->load_id = $destination['load_id'];
                $replica->user_id = $user->id;
                $replica->public_token = hash('sha256', Str::random(60));
                unset($replica->pickup_full_name, $replica->delivery_full_name, $replica->shipper_full_name);
                $replica->save();

                $order
                    ->vehicles()
                    ->each(
                        static function (Vehicle $vehicle) use ($replica, $destination): void {
                            if (!in_array($vehicle->id, $destination['vehicles'])) {
                                return;
                            }
                            $vehicle->order_id = $replica->id;
                            $vehicle->save();
                        }
                    );

                // add payment
                $pc = $order->payment->replicate();
                $replica->payment()->save($pc);

                // add expenses
                if ($order->expenses->count()) {
                    foreach ($order->expenses as $expense) {
                        $ec = $expense->replicate();
                        $ec->save();

                        if ($expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)) {
                            $ec->clearMediaCollection(Expense::EXPENSE_COLLECTION_NAME);
                            $ec->addMediaFromDisk($expense->getFirstMediaPath(Expense::EXPENSE_COLLECTION_NAME))
                                ->preservingOriginal()
                                ->usingName($expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)->name)
                                ->setFileName(
                                    media_hash_file(
                                        $expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)->getUrl(),
                                        $expense->getFirstMedia(Expense::EXPENSE_COLLECTION_NAME)->getExtensionAttribute()
                                    )
                                )->toMediaCollection(Expense::EXPENSE_COLLECTION_NAME);
                        }

                        $replica->expenses()->save($ec);
                    }
                }

                $replica->clearMediaCollection(Order::ATTACHMENT_COLLECTION_NAME);

                $order->getMedia(Order::ATTACHMENT_COLLECTION_NAME)
                    ->each(
                        static function (Media $media) use ($replica): void {
                            try {
                                $media->copy($replica, Order::ATTACHMENT_COLLECTION_NAME, $media->disk);
                            } catch (Throwable $e) {
                                return;
                            }
                        }
                    );

                $replica->save();

                EventService::order($replica)->user($this->user)->create()->push()->broadcast();
            }

            $event->user($this->user)->update()->push()->broadcast();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getOrderByPublicToken(string $token, ?OrderSignature &$signature = null): Order
    {
        $order = Order::where('public_token', $token)->first();

        if ($order) {
            return $order;
        }

        $signature = OrderSignature::where('signature_token', $token)->where(
            'created_at',
            '>=',
            Carbon::now()->subSeconds(config('orders.inspection.signature_bol_link_life'))
        )->first();

        if (!$signature) {
            throw new ModelNotFoundException();
        }

        return $signature->order;
    }

    public function publicBol(string $token)
    {
        $order = $this->getOrderByPublicToken($token, $signature);

        return !$signature ? $order : $signature;
    }

    /**
     * @param string $token
     * @throws Throwable
     */
    public function publicBolPrint(string $token): void
    {
        $this->getGeneratePdfService()->printOrder(
            $this->getOrderByPublicToken($token)
        );
    }

    private function getGeneratePdfService(): GeneratePdfService
    {
        return $this->generatePdfService;
    }

    /**
     * @param string $token
     * @param bool $showShipperInfo
     * @throws Throwable
     */
    public function publicPdfBol(string $token, bool $showShipperInfo = false): void
    {
        $this->getGeneratePdfService()->getBol(
            $this->getOrderByPublicToken($token),
            $showShipperInfo
        );
    }

    /**
     * @param string $token
     * @param array $invoice
     * @throws EmptyInvoiceTotalDue
     * @throws Throwable
     */
    public function publicPdfInvoice(string $token, array $invoice): void
    {
        $order = Order::where('public_token', $token)->firstOrFail();

        $this->getGeneratePdfService()->getInvoice($order, $invoice);
    }

    public function orderHistory(int $order_id)
    {
        $history = History::query()
            ->where(
                [
                    ['model_id', $order_id],
                    ['model_type', Order::class],
                ]
            )
            ->latest('performed_at')
            ->get();

        if ($history) {
            foreach ($history as &$h) {
                if (isset($h['meta']) && is_array($h['meta'])) {
                    $h['message'] = trans($h['message'], $h['meta']);
                }
            }
        }

        return $history;
    }

    /**
     * @param Order $order
     * @param array $paymentData
     * @return Order
     * @throws Throwable
     */
    public function addPaymentData(Order $order, array $paymentData): Order
    {
        try {
            if ($order->payment->driver_payment_data_sent) {
                return $order;
            }

            DB::beginTransaction();

            $event = EventService::order($order)->user($this->user);

            $order->payment->fill($paymentData);

            if (isset($paymentData[Order::DRIVER_PAYMENT_FIELD_NAME])) {
                $order->payment->addMediaWithRandomName(
                    Order::DRIVER_PAYMENT_COLLECTION_NAME,
                    $paymentData[Order::DRIVER_PAYMENT_FIELD_NAME],
                    true
                );
            }

            if (isset($paymentData['driver_payment_method_id'])) {
                $order->payment->driver_payment_timestamp = now()->timestamp;
            }

            $order->payment->driver_payment_data_sent = true;
            $order->payment->saveOrFail();

            $event->update()->broadcast();

            DB::commit();

            return $order;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @return Order
     * @throws Throwable
     */
    public function sendInWork(Order $order): Order
    {
        if ($order->has_pickup_inspection || $order->has_pickup_signature) {
            throw new Exception(trans('This order can\'t be sent in work.'));
        }

        try {
            $event = EventService::order($order);

            $order->pickup_date = Carbon::now()->timestamp;
            $order->saveOrFail();

            $event->user($this->user)
                ->update()
                ->broadcast()
                ->push();

            return $order;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    // inspection

    /**
     * @param Order $order
     * @param array $requestData
     * @return Order
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addDocument(Order $order, array $requestData): Order
    {
        try {
            $event = EventService::order($order);

            if (isset($requestData[Order::DRIVER_DOCUMENTS_FIELD_NAME])) {
                $order->addMediaWithRandomName(
                    Order::DRIVER_DOCUMENTS_COLLECTION_NAME,
                    $requestData[Order::DRIVER_DOCUMENTS_FIELD_NAME]
                );
            }

            $event->user($this->user)
                ->update(OrderEventService::ACTION_DRIVER_ATTACHED_DOCUMENT)
                ->broadcast();

            return $order;
        } catch (Throwable $e) {
            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param int $id
     * @throws MediaCannotBeDeleted
     */
    public function deleteDocument(Order $order, int $id): void
    {
        try {
            $media = $order->getMedia(Order::DRIVER_DOCUMENTS_COLLECTION_NAME)->where('id', $id)->first();

            if ($media) {
                $order->deleteMedia($id);

                EventService::order($order)
                    ->user($this->user)
                    ->update(OrderEventService::ACTION_DRIVER_DELETE_DOCUMENT)
                    ->broadcast();

                return;
            }

            throw new Exception(trans('Document not found.'));
        } catch (Throwable $e) {
            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param array $requestData
     * @return Order
     * @throws Exception
     */
    public function addPhoto(Order $order, array $requestData): Order
    {
        try {
            if (isset($requestData[Order::DRIVER_PHOTOS_FIELD_NAME])) {
                $order->addMediaWithRandomName(
                    Order::DRIVER_PHOTOS_COLLECTION_NAME,
                    $requestData[Order::DRIVER_PHOTOS_FIELD_NAME]
                );
            }

            EventService::order($order)
                ->user($this->user)
                ->update(OrderEventService::ACTION_DRIVER_ATTACHED_PHOTO)
                ->broadcast();

            return $order;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param int $id
     * @throws MediaCannotBeDeleted
     */
    public function deletePhoto(Order $order, int $id): void
    {
        try {
            $media = $order->getMedia(Order::DRIVER_PHOTOS_COLLECTION_NAME)->where('id', $id)->first();

            if ($media) {
                $order->deleteMedia($id);

                EventService::order($order)
                    ->user($this->user)
                    ->update(OrderEventService::ACTION_DRIVER_DELETE_PHOTO)
                    ->broadcast();

                return;
            }

            throw new Exception(trans('Photo not found.'));
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function validateExteriorInspection(Vehicle $vehicle, Inspection $inspection): bool
    {
        if ($vehicle->type_id) {
            foreach (range(1, $vehicle->getMinPhotoCount()) as $photo_id) {
                $collectionName = Order::INSPECTION_PHOTO_COLLECTION_NAME . '_' . $photo_id;
                if (!$inspection->getFirstMedia($collectionName)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    private function validatePickupInspection(Order $order): bool
    {
        if ($order->vehicles) {
            foreach ($order->vehicles as $vehicle) {
                if (
                    !$vehicle->pickupInspection
                    || !$vehicle->pickupInspection->has_vin_inspection
                    || (
                        $vehicle->pickupInspection->odometer === null
                        && !$vehicle->pickupInspection->notes
                    )
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateDeliveryInspection(Order $order): bool
    {
        if ($order->vehicles) {
            foreach ($order->vehicles as $vehicle) {
                if (
                    !$vehicle->deliveryInspection
                    || !$vehicle->deliveryInspection->has_vin_inspection
                    || (
                        $vehicle->deliveryInspection->odometer === null
                        && !$vehicle->deliveryInspection->notes
                    )
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param array $inspectionData
     * @return Vehicle
     * @throws Throwable
     */
    public function inspectVin(Order $order, Vehicle $vehicle, array $inspectionData): Vehicle
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }

        try {
            DB::beginTransaction();

            $vehicle->preserveOldValues();

            $inspectionData['vin'] = isset($inspectionData['vin'])
                ? mb_convert_case($inspectionData['vin'], MB_CASE_UPPER)
                : null;

            // create pickup inspection
            /** @var Inspection $pickupInspectionModel */
            $pickupInspectionModel = Inspection::query()->updateOrCreate(
                [
                    'id' => $vehicle->pickup_inspection_id,
                ],
                $inspectionData
            );

            if (!$vehicle->pickup_inspection_id) {
                $vehicle->pickupInspection()->associate($pickupInspectionModel);
            }

            if (isset($inspectionData[Order::VIN_SCAN_FIELD_NAME])) {
                $pickupInspectionModel->addMediaWithRandomName(
                    Order::VIN_SCAN_COLLECTION_NAME,
                    $inspectionData[Order::VIN_SCAN_FIELD_NAME],
                    true
                );

                $pickupInspectionModel->has_vin_inspection = true;
            } else {
                $pickupInspectionModel->clearMediaCollection(Order::VIN_SCAN_COLLECTION_NAME);

                $pickupInspectionModel->has_vin_inspection = true;
                $vehicle->vin = $pickupInspectionModel->vin;
                $vehicle->saveOrFail();
            }

            $pickupInspectionModel->saveOrFail();

            // create delivery inspection without vin photo
            /** @var Inspection $deliveryInspectionModel */
            $deliveryInspectionModel = Inspection::query()->updateOrCreate(
                [
                    'id' => $vehicle->delivery_inspection_id,
                ],
                $inspectionData
            );

            if (!$vehicle->delivery_inspection_id) {
                $vehicle->deliveryInspection()->associate($deliveryInspectionModel);
            }

            $deliveryInspectionModel->has_vin_inspection = $pickupInspectionModel->has_vin_inspection;
            $deliveryInspectionModel->saveOrFail();

            $vehicle->saveOrFail();

            DB::commit();

            return $vehicle;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Vehicle $vehicle
     * @param InspectDamageDto $dto
     * @return Vehicle
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws NotVinInspectionException
     */
    public function inspectPickupDamage(Vehicle $vehicle, InspectDamageDto $dto): Vehicle
    {
        if (!$vehicle->pickupInspection || !$vehicle->pickupInspection->has_vin_inspection) {
            throw new NotVinInspectionException;
        }

        try {
            $vehicle->pickupInspection->damage_labels = $dto->getDamageLabels();
            $vehicle->pickupInspection->save();

            $vehicle->pickupInspection->addMediaWithRandomName(
                Order::INSPECTION_DAMAGE_COLLECTION_NAME,
                $dto->getDamagePhoto(),
                true,
                true
            );

            $labeledImage = $this->imageService->addDamageLabels(
                TextLine::byParams(
                    implode(
                        "\n",
                        Inspection::decodeDamageLabels(
                            $dto->getDamageLabels([])
                        )
                    ),
                    null,
                    3
                ),
                $dto->getDamagePhoto()
            );

            $vehicle->pickupInspection->addMediaWithRandomName(
                Order::INSPECTION_DAMAGE_LABELED_COLLECTION_NAME,
                $labeledImage,
                true
            );

            return $vehicle;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param InspectExteriorDto $dto
     * @return Vehicle
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    public function inspectPickupExterior(Order $order, Vehicle $vehicle, InspectExteriorDto $dto): Vehicle
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }

        $inspection = $vehicle->pickupInspection;

        if (!$inspection->has_vin_inspection) {
            throw new Exception(trans('Vehicle vin inspection not finished.'));
        }

        try {
            $textString = sprintf(
                'Pickup: %s %s, Lat / Long: %s, %s',
                $dto->getTime()->format(config('images.inspection.date_format')),
                mb_convert_case($dto->getTime()->getTimezone()->getAbbr(), MB_CASE_UPPER),
                $dto->getLatitude(),
                $dto->getLongitude()
            );

            $image = $this->imageService->addTextOnImage(TextLine::byParams($textString), $dto->getPhoto());

            $collectionName = Order::INSPECTION_PHOTO_COLLECTION_NAME . '_' . $dto->getPhotoId();

            $inspection->addMediaWithRandomName(
                $collectionName,
                $image,
                true,
                false,
                [
                    'lat' => $dto->getLatitude(),
                    'lng' => $dto->getLongitude(),
                    'timestamp' => $dto->getTime()->timestamp,
                    'timezone' => $dto->getTime()->getTimezone()->getName(),
                ]
            );

            $inspection->saveOrFail();

            return $vehicle;
        } catch (Throwable $e) {
            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param int $photo_id
     * @return Vehicle
     * @throws Throwable
     */
    public function deletePickupPhoto(Order $order, Vehicle $vehicle, int $photo_id): Vehicle
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }

        try {
            $collectionName = Order::INSPECTION_PHOTO_COLLECTION_NAME . '_' . $photo_id;

            $vehicle->pickupInspection->clearMediaCollection($collectionName);

            $vehicle->pickupInspection->saveOrFail();

            return $vehicle;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param array $inspectionData
     * @return Vehicle
     * @throws Throwable
     */
    public function inspectPickupInterior(Order $order, Vehicle $vehicle, array $inspectionData): Vehicle
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }

        if (!$vehicle->pickupInspection->has_vin_inspection) {
            throw new Exception(trans('Vehicle vin inspection not finished.'));
        }

        try {
            DB::beginTransaction();

            $vehicle->pickupInspection->fill($inspectionData);
            $vehicle->pickupInspection->saveOrFail();

            DB::commit();

            return $vehicle;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param array $data
     * @param string $userTimezone
     * @return Order
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    public function pickupSignature(Order $order, array $data, ?string $userTimezone): Order
    {
        if (!$this->validatePickupInspection($order)) {
            throw new Exception(trans('Vehicle pickup inspection not finished.'));
        }

        try {
            DB::beginTransaction();

            $event = EventService::order($order);

            $order->pickup_customer_not_available = $data['customer_not_available'];
            $order->pickup_customer_refused_to_sign = $data['customer_refused_to_sign'];
            $order->pickup_customer_full_name = $data['customer_full_name'] ?? null;

            if (
                !$order->pickup_customer_not_available
                && !$order->pickup_customer_refused_to_sign
            ) {
                $order->addMediaWithRandomName(
                    Order::PICKUP_CUSTOMER_SIGNATURE_COLLECTION_NAME,
                    $data[Order::CUSTOMER_SIGNATURE_FIELD_NAME],
                    true
                );
            }

            $order->addMediaWithRandomName(
                Order::PICKUP_DRIVER_SIGNATURE_COLLECTION_NAME,
                $data[Order::DRIVER_SIGNATURE_FIELD_NAME],
                true
            );

            $order->has_pickup_signature = true;
            $order->has_pickup_inspection = true;
            $order->driver_pickup_id = $order->driver_id;

            $companyTimezone = $order->company->getTimezoneOrDefault();

            $order->pickup_date_actual = !empty($data['actual_date'])
                ? Carbon::createFromTimestamp($data['actual_date'], $userTimezone)
                    ->setTimezone('UTC')
                    ->getTimestamp()
                : Carbon::now('UTC')->getTimestamp();

            $order->pickup_date_data = [
                'actual_date' => $data['actual_date'],
                'timezone' => $userTimezone,
                'company_timezone' => $companyTimezone,
                'company_date' =>  $order->pickup_date_actual,
                'company_date_as_string' =>  !empty($data['actual_date'])
                    ? Carbon::createFromTimestamp($data['actual_date'], $userTimezone)
                        ->setTimezone($companyTimezone)
                        ->toDateTimeString()
                    : Carbon::now($companyTimezone)->toDateTimeString()
            ];
            $deliveryTimezone = $order->pickup_contact['timezone'] ?? $companyTimezone;
            $order->pickup_date_actual_tz = Carbon::createFromTimestamp($order->pickup_date_actual)
                ->setTimezone($deliveryTimezone);

            $order->saveOrFail();

            $event->user($this->user)
                ->update()
                ->broadcast()
                ->push();

            DB::commit();

            return $order;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error($e);
            throw $e;
        }
    }

    /**
     * @param Vehicle $vehicle
     * @param InspectDamageDto $dto
     * @return Vehicle
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    public function inspectDeliveryDamage(Vehicle $vehicle, InspectDamageDto $dto): Vehicle
    {
        $vehicle->createInspectionsIfNotExist();

        try {
            $vehicle->deliveryInspection->damage_labels = $dto->getDamageLabels();
            $vehicle->deliveryInspection->save();

            $vehicle->deliveryInspection->addMediaWithRandomName(
                Order::INSPECTION_DAMAGE_COLLECTION_NAME,
                $dto->getDamagePhoto(),
                true,
                true
            );

            $labeledImage = $this->imageService->addDamageLabels(
                TextLine::byParams(
                    implode(
                        "\n",
                        Inspection::decodeDamageLabels(
                            array_merge(
                                $dto->getDamageLabels([]),
                                $vehicle->pickupInspection->damage_labels ?? []
                            )
                        )
                    ),
                    null,
                    3
                ),
                $dto->getDamagePhoto()
            );

            $vehicle->deliveryInspection->addMediaWithRandomName(
                Order::INSPECTION_DAMAGE_LABELED_COLLECTION_NAME,
                $labeledImage,
                true
            );

            return $vehicle;
        } catch (Throwable $e) {
            Log::error($e);
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param InspectExteriorDto $dto
     * @return Vehicle
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    public function inspectDeliveryExterior(Order $order, Vehicle $vehicle, InspectExteriorDto $dto): Vehicle
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }

        $vehicle->createInspectionsIfNotExist();

        $inspection = $vehicle->deliveryInspection;

        try {
            $textString = sprintf(
                'Delivery: %s %s, Lat / Long: %s, %s',
                $dto->getTime()->format(config('images.inspection.date_format')),
                mb_convert_case($dto->getTime()->getTimezone()->getAbbr(), MB_CASE_UPPER),
                $dto->getLatitude(),
                $dto->getLongitude()
            );

            $image = $this->imageService->addTextOnImage(TextLine::byParams($textString), $dto->getPhoto());

            $collectionName = Order::INSPECTION_PHOTO_COLLECTION_NAME . '_' . $dto->getPhotoId();

            $inspection->addMediaWithRandomName(
                $collectionName,
                $image,
                true,
                false,
                [
                    'lat' => $dto->getLatitude(),
                    'lng' => $dto->getLongitude(),
                    'timestamp' => $dto->getTime()->timestamp,
                    'timezone' => $dto->getTime()->getTimezone()->getName(),
                ]
            );

            $inspection->saveOrFail();

            return $vehicle;
        } catch (Throwable $e) {
            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param int $photo_id
     * @return Vehicle
     * @throws Throwable
     */
    public function deleteDeliveryPhoto(Order $order, Vehicle $vehicle, int $photo_id): Vehicle
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }

        try {
            $collectionName = Order::INSPECTION_PHOTO_COLLECTION_NAME . '_' . $photo_id;

            $vehicle->deliveryInspection->clearMediaCollection($collectionName);

            $vehicle->deliveryInspection->saveOrFail();

            return $vehicle;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param Vehicle $vehicle
     * @param array $inspectionData
     * @return Vehicle
     * @throws Throwable
     */
    public function inspectDeliveryInterior(Order $order, Vehicle $vehicle, array $inspectionData): Vehicle
    {
        if ($vehicle->order_id !== $order->id) {
            throw new Exception(trans('Vehicle not found.'));
        }

        $vehicle->createInspectionsIfNotExist();

        try {
            DB::beginTransaction();

            $vehicle->deliveryInspection->fill($inspectionData);
            $vehicle->deliveryInspection->saveOrFail();

            DB::commit();

            return $vehicle;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param array $data
     * @param string $userTimezone
     * @return Order
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Throwable
     */
    public function deliverySignature(Order $order, array $data, ?string $userTimezone): Order
    {
        if (!$this->validateDeliveryInspection($order)) {
            throw new Exception(trans('Vehicle delivery inspection not finished.'));
        }

        try {
            DB::beginTransaction();

            $event = EventService::order($order);

            $order->delivery_customer_not_available = $data['customer_not_available'];
            $order->delivery_customer_refused_to_sign = $data['customer_refused_to_sign'];

            $order->delivery_customer_full_name = $data['customer_full_name'] ?? null;

            if (
                !$order->delivery_customer_not_available
                && !$order->delivery_customer_refused_to_sign
            ) {
                $order->addMediaWithRandomName(
                    Order::DELIVERY_CUSTOMER_SIGNATURE_COLLECTION_NAME,
                    $data[Order::CUSTOMER_SIGNATURE_FIELD_NAME],
                    true
                );
            }

            $order->addMediaWithRandomName(
                Order::DELIVERY_DRIVER_SIGNATURE_COLLECTION_NAME,
                $data[Order::DRIVER_SIGNATURE_FIELD_NAME],
                true
            );
            $order->has_delivery_signature = true;
            $order->has_delivery_inspection = true;
            $order->driver_delivery_id = $order->driver_id;

            $companyTimezone = $order->company->getTimezoneOrDefault();

            $order->delivery_date_actual = !empty($data['actual_date'])
                ? Carbon::createFromTimestamp($data['actual_date'], $userTimezone)
                    ->setTimezone($companyTimezone)
                    ->getTimestamp()
                : Carbon::now($companyTimezone)->getTimestamp();

            $order->delivery_date_data = [
                'actual_date' => $data['actual_date'],
                'timezone' => $userTimezone,
                'company_timezone' => $companyTimezone,
                'company_date' =>  $order->delivery_date_actual,
                'company_date_as_string' =>  !empty($data['actual_date'])
                    ? Carbon::createFromTimestamp($data['actual_date'], $userTimezone)
                        ->setTimezone($companyTimezone)
                        ->toDateTimeString()
                    : Carbon::now($companyTimezone)->toDateTimeString()
            ];

            $deliveryTimezone = $order->delivery_contact['timezone'] ?? $companyTimezone;
            $order->delivery_date_actual_tz = Carbon::createFromTimestamp($order->delivery_date_actual)
                ->setTimezone($deliveryTimezone);


            $order->saveOrFail();

            $event->user($this->user)
                ->update()
                ->push()
                ->broadcast();

            DB::commit();

            return $order;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $order
     * @param array $data
     * @return Order
     * @throws Throwable
     */
    public function completeInspection(Order $order, array $data): Order
    {
        try {
            DB::beginTransaction();

            $event = EventService::order($order);

            $inspection = $data['inspection_type'];

            $order->{'has_' . $inspection . '_signature'} = true;
            $order->{'has_' . $inspection . '_inspection'} = true;
            $order->{$inspection . '_date_actual'} = $data['actual_date'] ?? Carbon::now()->timestamp;

            $order->saveOrFail();

            if (!empty($data['bol_file'])) {
                $collection = $inspection === Order::LOCATION_DELIVERY ? Order::DELIVERY_DRIVER_INSPECTION_BOL_COLLECTION_NAME :
                    Order::PICKUP_DRIVER_INSPECTION_BOL_COLLECTION_NAME;

                $order->addMediaWithRandomName(
                    $collection,
                    $data['bol_file'],
                    true
                );
            }

            $event->user($this->user)
                ->update()
                ->broadcast()
                ->push();

            DB::commit();

            return $order;
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error($e);
            throw $e;
        }
    }

    // offers

    /**
     * @param Order $order
     * @param null $driver_id
     * @return Order
     * @throws Throwable
     */
    public function takeOrder(Order $order, $driver_id = null): Order
    {
        if ($order->dispatcher_id) {
            throw new Exception(trans('This order can\'t be taken.'));
        }

        try {
            $event = EventService::order($order);

            $order->dispatcher_id = $this->user->id;
            $order->driver_id = $driver_id;
            $order->saveOrFail();

            $event->user($this->user)
                ->update()
                ->push()
                ->broadcast();

            return $order;
        } catch (Throwable $e) {
            Log::error($e);

            throw $e;
        }
    }

    /**
     * @param Order $order
     * @return Order
     * @throws Throwable
     */
    public function releaseOrder(Order $order): Order
    {
        if ($order->dispatcher_id !== $this->user->id || !$order->isStatusNew()) {
            throw new OrderCantBeMovedOffers();
        }

        try {
            $event = EventService::order($order);

            $order->dispatcher_id = null;
            $order->driver_id = null;
            $order->status = Order::STATUS_NEW;

            $order->saveOrFail();

            $event->user($this->user)
                ->update()
                ->broadcast()
                ->push();

            return $order;
        } catch (Throwable $e) {
            Log::error($e);
            throw $e;
        }
    }

    public function changeDeductFromDriver(Order $order, array $validated): ?Order
    {
        try {
            $event = EventService::order($order);

            $order->update($validated);

            $event->user($this->user)
                ->deduct()
                ->broadcast();

            return $order;
        } catch (Throwable $e) {
            Log::error($e);
        }

        return null;
    }

    public function reassign(\Illuminate\Database\Eloquent\Collection $orders, User $user, bool $isDispatcher = true): void
    {
        if ($orders->isEmpty()) {
            return;
        }

        Order::whereIn('id', $orders->pluck('id'))
            ->update([($isDispatcher ? 'dispatcher_id' : 'driver_id') => $user->id]);


        EventService::order()->user($this->user)->reassign($orders)->broadcast()->push();
    }

    public function notifyReviewers(Order $order): void
    {
        if ($order->need_review && !$order->has_review) {
            $users = User::canCreateOrders()->where('can_check_orders', true)->get();

            $users->each(function (User $user) use ($order) {
                $this->getPushService()->pushUser($order, $user, PushNotificationTask::DISPATCHER_NEED_REVIEW_ONCE);

                $user->notify(
                    new AlertNotification(
                        $user->getCompanyId(),
                        'push.' . PushNotificationTask::DISPATCHER_NEED_REVIEW_ONCE,
                        AlertNotification::TARGET_TYPE_ORDER,
                        ['order_id' => $order->id],
                        ['load_id' => $order->load_id]
                    )
                );
            });
        }
    }

    /**
     * @throws Exception
     */
    public function addPaymentStage(Order $order, array $stageData, User $user): Model
    {
        try {
            DB::beginTransaction();

            $event = EventService::order($order);

            $stageData['payment_date'] = (
            new Carbon(
                $stageData['payment_date'],
                $user->getCompany()->getTimezone()
            )
            )->timestamp;

            $paymentStage = $order->paymentStages()->create($stageData);

            $event->user($this->user)
                ->update(OrderEventService::ACTION_PAYMENT_STAGE_ADDED)
                ->broadcast();

            DB::commit();

            return $paymentStage;
        } catch (Throwable $e) {
            DB::rollBack();
            throw($e);
        }
    }

    /**
     * @throws Exception
     */
    public function deletePaymentStage(Order $order, PaymentStage $paymentStage): void
    {
        if ($paymentStage->order_id !== $order->id) {
            throw new Exception(trans('Payment stage not found.'));
        }

        try {
            DB::beginTransaction();

            $event = EventService::order($order);

            $paymentStage->delete();

            $event->user($this->user)
                ->update(OrderEventService::ACTION_PAYMENT_STAGE_DELETED)
                ->broadcast();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            throw($e);
        }
    }

    /**
     * @param Order $order
     * @param string $location
     * @throws OrderAlreadySigned
     * @throws OrderHasNotHadInspectionYet
     */
    private function canSetCustomerSignature(Order $order, string $location): void
    {
        if (!$order->{'has_' . $location . '_signature'} || !$order->{'has_' . $location . '_inspection'}) {
            throw new OrderHasNotHadInspectionYet($location);
        }

        if (!$order->{$location . '_customer_refused_to_sign'} && !$order->{$location . '_customer_not_available'}) {
            throw new OrderAlreadySigned($location);
        }
    }

    public function necessarySendSignatureLink(Order $order, User $user): ?array
    {
        if (!$user->can('orders send-signature-link')) {
            return null;
        }

        foreach ([Order::LOCATION_PICKUP, Order::LOCATION_DELIVERY] as $location) {
            try {
                $this->canSetCustomerSignature($order, $location);
                $result[] = $location;
            } catch (Throwable $e) {
                continue;
            }
        }

        return !empty($result) ? $result : null;
    }

    /**
     * @param Order $order
     * @param array $signatureData
     * @throws OrderAlreadySigned
     * @throws OrderHasNotHadInspectionYet
     */
    public function sendSignatureLink(Order $order, array $signatureData): void
    {
        $this->canSetCustomerSignature($order, $signatureData['inspection_location']);

        try {
            /**@var OrderSignature $signature*/
            $signature = $order->signatures()
                ->where('email', $signatureData['email'])
                ->where('inspection_location', $signatureData['inspection_location'])
                ->first();

            if (!$signature) {
                $signature = $order->signatures()->make([
                    'user_id' => $this->user->id,
                    'inspection_location' => $signatureData['inspection_location'],
                    'signature_token' => hash('sha256', Str::random(60))
                ]);
            }

            $signature->email = $signatureData['email'];

            $signature->created_at = $signature->updated_at = time();

            $signature->save();

            Notification::route('mail', $signature->email)
                ->notify(new SendSignatureLink($signature));

            event(
                new ModelChanged(
                    $order,
                    'history.sent_signature_link',
                    [
                        'location' => $signature->inspection_location,
                        'full_name' => $signature->sender->full_name,
                        'email_sender' => $signature->sender->email,
                        'email_recipient' => $signature->email
                    ]
                )
            );
        } catch (Throwable $e) {
            Log::error($e);

            throw $e;
        }
    }


    public function getSignatureType(OrderSignature $signature): ?string
    {
        $order = $signature->order;

        try {
            $this->canSetCustomerSignature($order, $signature->inspection_location);
        } catch (OrderHasNotHadInspectionYet | OrderAlreadySigned $e) {
            return null;
        }

        if ($signature->created_at->getTimestamp() + config('orders.inspection.signature_bol_link_life') < Carbon::now()->timestamp) {
            return null;
        }

        if ($signature->signed) {
            return null;
        }

        return $signature->inspection_location;
    }

    /**
     * @param string $token
     * @param array $data
     * @throws HaveToAgreeWithInspection
     * @throws OrderSignatureLinkExpired
     * @throws Throwable
     */
    public function signPublicBol(string $token, array $data): void
    {
        try {
            $this->getOrderByPublicToken($token, $signature);

            if (!$signature) {
                throw new OrderSignatureLinkExpired();
            }
        } catch (ModelNotFoundThrowable $e) {
            throw new OrderSignatureLinkExpired();
        }

        $location = $this->getSignatureType($signature);

        if ($location === null) {
            throw new OrderSignatureLinkExpired();
        }

        if (empty($data['inspection_agree'])) {
            throw new HaveToAgreeWithInspection($location);
        }

        try {
            DB::beginTransaction();

            /**@var OrderSignature $signature */
            $signature->last_name = $data['last_name'];
            $signature->first_name = $data['first_name'];
            $signature->signed_time = Carbon::createFromFormat('m/d/Y g:i A', $data['signed_time'])->toDateTimeString();
            $signature->signed = true;
            $signature->save();

            $order = $signature->order;

            $event = EventService::order($order);

            $order->{$location . '_customer_refused_to_sign'} = false;
            $order->{$location . '_customer_not_available'} = false;
            $order->{$location . '_customer_full_name'} = $signature->first_name . ' ' . $signature->last_name;

            $order->save();

            $order->addMediaWithRandomName(
                $location === Order::LOCATION_DELIVERY ? Order::DELIVERY_CUSTOMER_SIGNATURE_COLLECTION_NAME : Order::PICKUP_CUSTOMER_SIGNATURE_COLLECTION_NAME,
                $data['sign_file'],
                true
            );

            $event->signedInspection($signature)->broadcast();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function export(Carbon $dateFrom, Carbon $dateTo): BinaryFileResponse
    {
        $orders = Order::where(Order::TABLE_NAME . '.created_at', '>=', $dateFrom)
            ->where(Order::TABLE_NAME . '.created_at', '<=', $dateTo)
            ->orderBy(Order::TABLE_NAME . '.created_at', 'desc')
            ->get();

        $filename = sprintf(
            'orders-%s_%s.xlsx',
            $dateFrom->format('Y-m-d'),
            $dateTo->format('Y-m-d')
        );

        return Excel::download(new OrderExport($orders), $filename);
    }

    public function paymentForDistance(Order $order, bool $save = false)
    {
        if(
            $order->getPickupContactAsStr()
            && $order->getDeliveryContactAsStr()
        ){

            $data = [
                'origin' => $order->getPickupContactAsStr(),
                'destination' => $order->getDeliveryContactAsStr(),
            ];

            try {
                /** @var $command GetDistanceBetweenAddresses */
                $command = resolve(GetDistanceBetweenAddresses::class);
                $res = $command->handler($data);

                $order->distance_data = $res;

                if($save) $order->save();

            } catch (Throwable $e){
                logger_info("SET DISTANCE BETWEEN ADDRESS FAIL", [$e->getMessage()]);
            }
        }
    }
}
