<?php

namespace App\Http\Requests\Orders\BS;

use App\Enums\Orders\BS\OrderStatus;
use App\Models\Orders\BS\Order;
use App\Repositories\Orders\BS\OrderRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="OrderChangeStatusRequest",
 *     required={"status"},
 *     @OA\Property(property="status", type="string", example="in_process", enum={"new", "in_process", "finished"}),
 * )
 */

class OrderChangeStatusRequest extends FormRequest
{
    protected Order|null $order = null;

    public function rules(): array
    {
        return [
            'status' => ['required', 'string',
                Rule::in($this->statusIn()),
                function ($attribute, $value, $fail) {
                    if (!$this->getOrder()->isStatusCanBeChanged()) {
                        $fail(__('exceptions.orders.status_cant_be_change'));
                    }
                }
            ],
        ];
    }

    private function statusIn(): array
    {
        $statusesArr = [
            OrderStatus::New->value => [
                OrderStatus::In_process->value,
                OrderStatus::Finished->value,
            ],
            OrderStatus::In_process->value => [
                OrderStatus::New->value,
                OrderStatus::Finished->value,
            ],
            OrderStatus::Finished->value => [
                OrderStatus::New->value,
                OrderStatus::In_process->value,
            ],
        ];

        return $statusesArr[$this->getOrder()->status->value];
    }

    public function getOrder(): Order
    {
        if(!$this->order){
            /** @var $repo OrderRepository */
            $repo = resolve(OrderRepository::class);

            /** @var $model Order */
            $this->order = $repo->getBy(
                ['id' => $this->route('id')],
                withException: true,
                exceptionMessage: __("exceptions.orders.bs.not_found"),
            );
        }

        return $this->order;
    }
}

