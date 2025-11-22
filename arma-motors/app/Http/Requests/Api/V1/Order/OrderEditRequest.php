<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(type="object", title="Order Edit Requestr",
 *     @OA\Property(property="status", title="Status", example="3",
 *         description="Статусы для заявки, допустивые значения: 1 - заявка создана, 2 - заявка в процессе работы, 3 - заявка выполнена"
 *     ),
 *     @OA\Property(property="statusPayment", title="Status payment", example="3",
 *         description="Статусы по оплате для заявки, допустивые значения: - 1 - не оплачено, 2 - оплачено частично, 3 - оплачено полностью"
 *     ),
 *     @OA\Property(property="responsible", title="Responsible", example="Иван Иванов",
 *         description="имя ответсвенного"
 *     ),
 *     @OA\Property(property="realDate", title="Real Date", example=163113480,
 *         description="Время для записи на заявку, если было изменено в альфа-авто"
 *     )
 * )
 */

class OrderEditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'  => ['required', 'integer', 'in:' . Status::CREATED .','. Status::IN_PROCESS .','. Status::DONE .','. Status::REJECT .','. Status::CLOSE .','. Status::DRAFT],
            'statusPayment'  => ['nullable', 'integer', 'in:'. PaymentStatus::NOT .','. PaymentStatus::PART .','. PaymentStatus::FULL],
            'responsible'  => ['nullable', 'string'],
            'realDate'  => ['nullable', 'integer'],
        ];
    }
}
