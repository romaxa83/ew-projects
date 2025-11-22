<?php

namespace App\Http\Requests\Api\V1\Agreement;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for create or update agreement",
 *     required={"id", "client", "auto", "phone", "number", "VIN"}
 * )
 */
class AgreementCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'  => ['required', 'string'],
            'client'  => ['required', 'string'],
            'auto'  => ['required', 'string'],
            'phone'  => ['required', 'string'],
            'number'  => ['required', 'string'],
            'VIN'  => ['required', 'string'],
            'author'  => ['nullable', 'string'],
            'authorPhone'  => ['nullable', 'string'],
            'base'  => ['nullable', 'string'],
            'idRequst'  => ['nullable', 'string'],
            'jobs'  => ['nullable', 'array'],
            'jobs.*.name'  => ['required', 'string'],
            'jobs.*.sum'  => ['required'],
            'parts'  => ['nullable', 'array'],
            'parts.*.name'  => ['required', 'string'],
            'parts.*.quantity'  => ['required'],
            'parts.*.sum'  => ['required'],
        ];

        /**
         * @OA\Property(property="id", title="ID", description="ID согласования", example="10266ab2-7cec-11ec-8277-4cd98fc26f14")
         * @OA\Property(property="client", title="Client", description="ID пользователя", example="20266ab2-7cec-11ec-8277-4cd98fc26f14")
         * @OA\Property(property="auto", title="Auto", description="ID автомобиля", example="30266ab2-7cec-11ec-8277-4cd98fc26f14")
         * @OA\Property(property="phone", title="Phone", description="Телефон", example="+380502051123")
         * @OA\Property(property="number", title="Number", description="Гос. номер", example="AA1071PB")
         * @OA\Property(property="VIN", title="Vin code", description="Вин код", example="VF1HSRADG582987")
         * @OA\Property(property="author", title="Author", description="Автор согласования", example="Аудит Софт")
         * @OA\Property(property="authorPhone", title="Author phone", description="Телефон автора согласования", example="+789789123123")
         * @OA\Property(property="base", title="Base", description="Алиас дилерского центра", example="arma-motors-renault")
         * @OA\Property(property="idRequst", title="idRequst", description="Uuid заявки, на основе которой создано доп. согласования", example="76e6f86a-a9cb-11ec-827c-4cd98fc26f14")
         * @OA\Property(property="jobs", title="Jobs", type="array",  @OA\Items(
         *      @OA\Property(property="name", title="Name", description="Название", example="АКБ заміна"),
         *      @OA\Property(property="sum", title="Sum", description="Сумма", example=165.6)
         *  ))
         * @OA\Property(property="parts", title="Parts", type="array",  @OA\Items(
         *      @OA\Property(property="name", title="Name", description="Название", example="Аккумулятор 70Ah 720А"),
         *      @OA\Property(property="sum", title="Sum", description="Сумма", example=3216.1),
         *      @OA\Property(property="quantity", title="Quantity", description="Кол-во", example=1)
         *  ))
         */
    }
}


