<?php

namespace App\Dto\Orders;

use App\Dto\BaseDto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

/**
 * @property-read int|null $id
 * @property-read int $typeId
 * @property-read float $price
 * @property-read Carbon $date
 * @property-read UploadedFile|null $receiptFile
 * @property-read string|null $to
 */
class ExpenseDto extends BaseDto
{
    protected ?int $id;
    protected int $typeId;
    protected float $price;
    protected Carbon $date;
    protected ?UploadedFile $receiptFile;
    protected ?string $to;

    /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
    public static function init(array $args): self
    {
        $dto = new self();

        $dto->id = $args['id'] ?? null;
        $dto->typeId = $args['type_id'];
        $dto->price = (float)$args['price'];
        $dto->date = Carbon::createFromFormat('m/d/Y', $args['date']);
        $dto->receiptFile = $args['receipt_file'] ?? null;
        $dto->to = $args['to'] ?? null;

        return $dto;
    }

    protected function renderCarbonValue(Carbon $carbon): int
    {
        return $carbon->getTimestamp();
    }
}
