<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Models\AA\AAPost;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Models\Order\Order;
use App\Models\User\Car;
use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for set exist order",
 *     required={"startdate", "enddate", "workshop"}
 * )
 */
class AAOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
//            'id' => ['nullable', 'string', "exists:".Order::TABLE_NAME.",uuid"],
            'id' => ['nullable', 'string'],
//            'client' => ['nullable', 'string', "exists:". User::TABLE_NAME.",uuid"],
            'client' => ['nullable', 'string'],
            'auto' => ['nullable', 'string'],
//            'auto' => ['nullable', 'string', "exists:".Car::TABLE_NAME.",uuid"],
            'type' => ['nullable', 'string', "exists:".Service::TABLE.",alias"],
            'subtype' => ['nullable', 'string', "exists:".Service::TABLE.",alias"],
            'base' => ['nullable', 'string', "exists:".Dealership::TABLE.",alias"],
            'startdate' => ['required', 'string'],
            'enddate' => ['required', 'string'],
            'workshop' => ['required', 'string', "exists:".AAPost::TABLE.",uuid"],
            'comment' => ['nullable', 'string'],
            'planning' => ['array'],
            'planning.*.startDate'  => ['required', 'string'],
            'planning.*.endDate'  => ['required', 'string'],
            'planning.*.workshop'  => ['required', 'string'],
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID заявки", example="10266ab2-7cec-11ec-8277-4cd98fc26f14")
     * @OA\Property(property="client", title="Client", description="ID пользователя", example="20266ab2-7cec-11ec-8277-4cd98fc26f14")
     * @OA\Property(property="auto", title="Auto", description="ID автомобиля", example="30266ab2-7cec-11ec-8277-4cd98fc26f14")
     * @OA\Property(property="type", title="Type", description="Алиас сервиса", example="spares")
     * @OA\Property(property="subtype", title="Sub type", description="Алиас сервиса", example="to")
     * @OA\Property(property="base", title="Base", description="Алиа дц", example="arma-motors-renault")
     * @OA\Property(property="startdate", title="Start date", description="Дата начало заявки", example="2021-09-01T16:53:40")
     * @OA\Property(property="enddate", title="End date", description="Дата конца заявки", example="2021-09-01T17:27:00")
     * @OA\Property(property="workshop", title="Workshop", description="ID поста", example="3b5bb1d4-58f3-11ec-8277-4cd98fc26f14")
     * @OA\Property(property="comment", title="Comment", description="Комментарий", example="Нова заявка ТЕСТ")
     * @OA\Property(property="planning", title="Planing", type="array",  @OA\Items(
     *      @OA\Property(property="startDate", title="Start date", description="Дата начало заявки", example="2021-09-01T16:53:40"),
     *      @OA\Property(property="endDate", title="End date", description="Дата конца заявки", example="2021-09-01T16:53:40"),
     *      @OA\Property(property="workshop", title="Workshop", description="ID поста", example="3b5bb1d4-58f3-11ec-8277-4cd98fc26f14")
     *  ))
     */
}
