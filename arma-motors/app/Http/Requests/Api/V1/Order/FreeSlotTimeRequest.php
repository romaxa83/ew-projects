<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Http\Requests\Rules\ExistDealership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for free slot time",
 *     required={"id", "name", "alias", "schedule"}
 * )
 */
class FreeSlotTimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.id' => ['required', 'string'],
            'data.name' => ['required', 'string'],
//            'data.alias' => ['required', 'string', "exists:".Dealership::TABLE.",alias"],
            'data.alias' => ['required', 'string', new ExistDealership()],
            'data.schedule.*.date'  => ['required', 'string'],
            'data.schedule.*.startDate'  => ['required', 'string'],
            'data.schedule.*.endDate'  => ['required', 'string'],
            'data.schedule.*.workingDay'  => ['required', 'boolean'],
        ];
    }

    /**
     *  @OA\Property(property="data", title="Data", type="object",
     *     @OA\Property(property="id", title="ID", description="ID поста", example="3c13fafb-79d6-11ec-8277-4cd98fc26f14"),
     *     @OA\Property(property="name", title="Name", description="Название поста", example="Виготовка П №1"),
     *     @OA\Property(property="alias", title="Alias", description="Алиас дц", example="arma-motors-renault"),
     *     @OA\Property(property="schedule", title="Schedule", type="array",  @OA\Items(
     *          @OA\Property(property="date", title="Start date", description="Дата дня", example="2021-09-01T00:00:00"),
     *          @OA\Property(property="startDate", title="Start date", description="Начало рабочего дня", example="2021-09-01T08:00:00"),
     *          @OA\Property(property="endDate", title="End date", description="Конец рабочего дня", example="2021-09-01T20:00:00"),
     *          @OA\Property(property="workingDay", title="Working day", description="Рабочий ли день", example=true),
     *  ))
     * )
     */
}
