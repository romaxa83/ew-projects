<?php

namespace App\Dto\Orders;

use App\Dto\BaseDto;
use App\Dto\Contacts\ContactDto;
use App\Dto\Contacts\TimeDto;
use App\Http\Controllers\Api\Helpers\DateTimeHelper;
use App\Models\Orders\Order;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property-read string $loadId
 * @property-read int|null $dispatcherId
 * @property-read int|null $driverId
 * @property-read int $inspectionType
 * @property-read string|null $instructions
 * @property-read string|null $dispatchInstructions
 * @property-read bool $needReview
 * @property-read Collection<UploadedFile>|UploadedFile[]|null $attachments
 * @property-read ContactDto $pickupContact
 * @property-read ContactDto $deliveryContact
 * @property-read ContactDto $shipperContact
 * @property-read bool $shipperCopyDelivery
 * @property-read Carbon|null $pickupDate
 * @property-read string|null $pickupBuyerNameNumber
 * @property-read TimeDto|null $pickupTime
 * @property-read string|null $pickupComment
 * @property-read bool $pickupSaveContact
 * @property-read Carbon|null $deliveryDate
 * @property-read TimeDto|null $deliveryTime
 * @property-read string|null $deliveryComment
 * @property-read bool $deliverySaveContact
 * @property-read string|null $shipperComment
 * @property-read bool $shipperSaveContact
 * @property-read Collection<VehicleDto>|VehicleDto[] $vehicles
 * @property-read PaymentDto $payment
 * @property-read Collection<ExpenseDto>|ExpenseDto[]|null $expenses
 * @property-read Collection<BonusDto>|BaseDto[]|null $bonuses
 * @property-read Collection<int>|int[]|null $tags
 */
class OrderDto extends BaseDto
{
    protected string $loadId;
    protected ?int $dispatcherId;
    protected ?int $driverId;
    protected int $inspectionType;
    protected ?string $instructions;
    protected ?string $dispatchInstructions;
    protected bool $needReview;
    protected ?Collection $attachments;
    protected ContactDto $pickupContact;
    protected ContactDto $deliveryContact;
    protected ContactDto $shipperContact;
    protected bool $shipperCopyDelivery;
    protected ?Carbon $pickupDate;
    protected ?string $pickupBuyerNameNumber;
    protected ?TimeDto $pickupTime;
    protected ?string $pickupComment;
    protected bool $pickupSaveContact;
    protected ?Carbon $deliveryDate;
    protected ?TimeDto $deliveryTime;
    protected ?string $deliveryComment;
    protected bool $deliverySaveContact;
    protected ?string $shipperComment;
    protected bool $shipperSaveContact;
    protected Collection $vehicles;
    protected PaymentDto $payment;
    protected ?Collection $expenses = null;
    protected ?Collection $bonuses = null;
    protected ?Collection $tags;

    public static function init(array $args): self
    {
        $dto = new self();

        $dto->loadId = $args['load_id'];
        $dto->dispatcherId = $args['dispatcher_id'] ?? null;
        $dto->driverId = $args['driver_id'] ?? null;
        $dto->inspectionType = $args['inspection_type'];
        $dto->instructions = $args['instructions'] ?? null;
        $dto->dispatchInstructions = $args['dispatch_instructions'] ?? null;
        $dto->needReview = !empty($args['need_review']);
        $dto->attachments = !empty($args[Order::ATTACHMENT_FIELD_NAME]) ? collect($args[Order::ATTACHMENT_FIELD_NAME]) : null;
        $dto->pickupContact = ContactDto::init($args['pickup_contact']);
        $dto->deliveryContact = ContactDto::init($args['delivery_contact']);
        $dto->shipperCopyDelivery = !empty($args['shipper_copy_delivery']);
        $dto->shipperContact = !$dto->shipperCopyDelivery ? ContactDto::init(
            $args['shipper_contact']
        ) : $dto->deliveryContact;
        $dto->pickupDate = !empty($args['pickup_date']) ?
            DateTimeHelper::fromDate($args['pickup_date'], $dto->pickupContact->timezone) :
            null;
        $dto->deliveryDate = !empty($args['delivery_date']) ?
            DateTimeHelper::fromDate($args['delivery_date'], $dto->deliveryContact->timezone) :
            null;
        $dto->pickupBuyerNameNumber = $args['pickup_buyer_name_number'] ?? null;
        $dto->pickupComment = $args['pickup_comment'] ?? null;
        $dto->deliveryComment = $args['delivery_comment'] ?? null;
        $dto->shipperComment = $args['shipper_comment'] ?? null;
        $dto->pickupSaveContact = !empty($args['pickup_save_contact']);
        $dto->deliverySaveContact = !empty($args['delivery_save_contact']);
        $dto->shipperSaveContact = !empty($args['shipper_save_contact']);
        $dto->pickupTime = !empty($args['pickup_time']) ? TimeDto::init($args['pickup_time']) : null;
        $dto->deliveryTime = !empty($args['delivery_time']) ? TimeDto::init($args['delivery_time']) : null;
        $dto->payment = PaymentDto::init($args['payment']);
        $dto->tags = !empty($args['tags']) ? collect($args['tags']) : null;
        $dto->vehicles = collect();
        foreach ($args['vehicles'] as $vehicle) {
            $dto->vehicles->push(VehicleDto::init($vehicle));
        }
        if (!empty($args['expenses'])) {
            $dto->expenses = collect();
            foreach ($args['expenses'] as $expense) {
                $dto->expenses->push(ExpenseDto::init($expense));
            }
        }
        if (!empty($args['bonuses'])) {
            $dto->bonuses = collect();
            foreach ($args['bonuses'] as $bonus) {
                $dto->bonuses->push(BonusDto::init($bonus));
            }
        }
        return $dto;
    }
}
