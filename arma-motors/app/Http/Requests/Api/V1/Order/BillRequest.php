<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for the order bill"
 * )
 */
class BillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parts' => ['array'],
            'parts.*.sum' => ['nullable'],
            'parts.*.ref' => ['nullable', 'string'],
            'parts.*.discountedPrice' => ['nullable'],
            'parts.*.name' => ['nullable', 'string'],
            'parts.*.price' => ['nullable'],
            'parts.*.quantity' => ['nullable'],
            'parts.*.unit' => ['nullable', 'string'],
            'parts.*.rate' => ['nullable'],
            'contactInformation' => ['nullable', 'string'],
            'date' => ['nullable', 'string'],
            'organization' => ['nullable', 'string'],
            'number' => ['nullable', 'string'],
            'shopper' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'etc' => ['nullable', 'string'],
            'taxCode' => ['nullable', 'string'],
            'discount' => ['nullable'],
            'amountWithoutVAT' => ['nullable'],
            'amountVAT' => ['nullable'],
            'amountIncludingVAT' => ['nullable'],
            'author' => ['nullable'],
        ];
    }

    /**
     *  @OA\Property(property="parts", title="parts", type="array",  @OA\Items(
     *      @OA\Property(property="sum", title="sum", example= 1135.3 ),
     *      @OA\Property(property="ref", title="ref", example="MR968274" ),
     *      @OA\Property(property="discountedPrice", title="discountedPrice", example=1135.3 ),
     *      @OA\Property(property="name", title="name", example="ФІЛЬТР ПОВІТРЯНИЙ" ),
     *      @OA\Property(property="price", title="price", example=1261.44 ),
     *      @OA\Property(property="quantity", title="quantity", type="string", example=1),
     *      @OA\Property(property="unit", title="unit", example="шт" ),
     *      @OA\Property(property="rate", title="rate", example=9.999683 ),
     *  ))
     *  @OA\Property(property="contactInformation", title="contactInformation", example="07400, Україна, Київська область, місто Бровари, вулиця Старотроїцька, будинок №42")
     *  @OA\Property(property="date", title="date", example="13.09.2021")
     *  @OA\Property(property="organization", title="organization", example="ФОП Барабаш Ю.О.")
     *  @OA\Property(property="number", title="number", example="VSK0150970")
     *  @OA\Property(property="shopper", title="number", example="Рильська Тетяна Олександрівна")
     *  @OA\Property(property="address", title="address", example="Світанкова, будинок №5")
     *  @OA\Property(property="phone", title="phone", example="+380939838323")
     *  @OA\Property(property="etc", title="etc", example="")
     *  @OA\Property(property="taxCode", title="taxCode", example="")
     *  @OA\Property(property="discount", title="discount", example=1208.76)
     *  @OA\Property(property="amountWithoutVAT", title="amountWithoutVAT", example=7266)
     *  @OA\Property(property="amountVAT", title="amountVAT", example=1211.02)
     *  @OA\Property(property="amountIncludingVAT", title="amountIncludingVAT", example=7266)
     *  @OA\Property(property="author", title="author", example="")
     */
}

