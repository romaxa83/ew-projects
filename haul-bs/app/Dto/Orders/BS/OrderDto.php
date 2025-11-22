<?php

namespace App\Dto\Orders\BS;

use App\Models\Orders\BS\Order;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class OrderDto
{
    public string|int|null $truckId;
    public string|int|null $trailerId;
    public float|null $discount;
    public float|null $taxInventory;
    public float|null $taxLabor;
    public Carbon $implementationDate;
    public string $dueDate;
    public string|int $mechanicId;
    public string|null $notes;
    public bool $needToUpdatePrices;

    /** @var array<int, OrderTypeOfWorkDto> */
    public array $typeOfWorks = [];

    /** @var array<int, UploadedFile> */
    public array $files = [];

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->truckId = $data['truck_id'] ?? null;
        $self->trailerId = $data['trailer_id'] ?? null;
        $self->discount = $data['discount'] ?? null;
        $self->taxInventory = $data['tax_inventory'] ?? null;
        $self->taxLabor = $data['tax_labor'] ?? null;
        $self->implementationDate = from_bs_timezone('Y-m-d H:i', $data['implementation_date']);
        $self->mechanicId = $data['mechanic_id'];
        $self->notes = $data['notes'] ?? null;
        $self->dueDate = $data['due_date'] ?? null;
        $self->needToUpdatePrices = $data['need_to_update_prices'] ?? false;

        foreach ($data['types_of_work'] ?? [] as $item){
            $self->typeOfWorks[] = OrderTypeOfWorkDto::byArgs($item);
        }

        $self->files = $data[Order::ATTACHMENT_FIELD_NAME] ?? [];

        return $self;
    }
}
