<?php

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Request for the order act"
 * )
 */
class ActRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jobsAmountVAT' => ['nullable', 'string'],
            'payer.name' => ['nullable', 'string'],
            'payer.date' => ['nullable', 'string'],
            'payer.contract' => ['nullable', 'string'],
            'payer.number' => ['nullable', 'string'],
            'repairType' => ['nullable', 'string'],
            'number' => ['nullable', 'string'],
            'closingDate' => ['nullable', 'string'],
            'organization.name' => ['nullable', 'string'],
            'organization.phone' => ['nullable', 'string'],
            'organization.address' => ['nullable', 'string'],
            'dealer' => ['nullable', 'string'],
            'jobs' => ['array'],
            'jobs.*.ref' => ['nullable', 'string'],
            'jobs.*.name' => ['nullable', 'string'],
            'jobs.*.coefficient' => ['nullable'],
            'jobs.*.priceWithVAT' => ['nullable', 'string'],
            'jobs.*.priceWithoutVAT' => ['nullable', 'string'],
            'jobs.*.amountWithoutVAT' => ['nullable', 'string'],
            'jobs.*.price' => ['nullable', 'string'],
            'jobs.*.amountIncludingVAT' => ['nullable', 'string'],
            'jobs.*.rate' => ['nullable'],
            'AmountInWords' => ['nullable', 'string'],
            'date' => ['nullable', 'string'],
            'mileage' => ['nullable', 'integer'],
            'currentAccount' => ['nullable', 'string'],
            'owner.name' => ['nullable', 'string'],
            'owner.phone' => ['nullable', 'string'],
            'owner.address' => ['nullable', 'string'],
            'owner.email' => ['nullable', 'string'],
            'owner.etc' => ['nullable', 'string'],
            'owner.certificate' => ['nullable', 'string'],
            'partsAmountIncludingVAT' => ['nullable', 'string'],
            'customer.name' => ['nullable', 'string'],
            'customer.FIO' => ['nullable', 'string'],
            'customer.phone' => ['nullable', 'string'],
            'customer.email' => ['nullable', 'string'],
            'customer.date' => ['nullable', 'string'],
            'customer.number' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
            'bodyNumber' => ['nullable', 'string'],
            'dateOfSale' => ['nullable', 'string'],
            'stateNumber' => ['nullable', 'string'],
            'producer' => ['nullable', 'string'],
            'dispatcher.position' => ['nullable', 'string'],
            'dispatcher.name' => ['nullable', 'string'],
            'dispatcher.date' => ['nullable', 'string'],
            'dispatcher.number' => ['nullable', 'string'],
            'dispatcher.FIO' => ['nullable', 'string'],
            'parts' => ['array'],
            'parts.*.unit' => ['nullable', 'string'],
            'parts.*.producer' => ['nullable', 'string'],
            'parts.*.ref' => ['nullable', 'string'],
            'parts.*.name' => ['nullable', 'string'],
            'parts.*.price' => ['nullable'],
            'parts.*.quantity' => ['nullable'],
            'parts.*.priceWithVAT' => ['nullable'],
            'parts.*.priceWithoutVAT' => ['nullable'],
            'parts.*.rate' => ['nullable'],
            'parts.*.amountWithoutVAT' => ['nullable'],
            'parts.*.amountIncludingVAT' => ['nullable'],
            'disassembledParts' => ['nullable', 'string'],
            'AmountIncludingVAT' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'AmountVAT' => ['nullable', 'string'],
            'discountParts' => ['nullable', 'string'],
            'discountJobs' => ['nullable', 'string'],
            'discount' => ['nullable', 'string'],
            'jobsAmountWithoutVAT' => ['nullable', 'string'],
            'jobsAmountIncludingVAT' => ['nullable', 'string'],
            'partsAmountWithoutVAT' => ['nullable', 'string'],
            'partsAmountVAT' => ['nullable', 'string'],
            'AmountWithoutVAT' => ['nullable', 'string'],
        ];
    }

    /**
     * @OA\Property(property="jobsAmountVAT", title="jobsAmountVAT", example="308,63")
     * @OA\Property(property="payer", title="payer", type="object",
     *      @OA\Property(property="name", title="name", example="Рильська Тетяна Олександрівна" ),
     *      @OA\Property(property="date", title="date", example="13.09.2021" ),
     *      @OA\Property(property="contract", title="contract", example="Замовлення на обслуговування" ),
     *      @OA\Property(property="number", title="number", example="ARM0108925" ),
     *  )
     * @OA\Property(property="repairType", title="repairType", example="Ремонтні роботи")
     * @OA\Property(property="number", title="number", example="ARM0108925")
     * @OA\Property(property="closingDate", title="closingDate", example="")
     * @OA\Property(property="organization", title="organization", type="object",
     *      @OA\Property(property="name", title="name", example="ФОП Барабаш Ю.О." ),
     *      @OA\Property(property="phone", title="phone", example="тел. ", ),
     *      @OA\Property(property="address", title="address", example="Україна, Київська область, м.Бровари, вул. Оболонська, будинок №72" ),
     *  )
     * @OA\Property(property="dealer", title="dealer", example="")
     * @OA\Property(property="jobs", title="jobs", type="array",  @OA\Items(
     *      @OA\Property(property="ref", title="ref", example="0008" ),
     *      @OA\Property(property="name", title="name", example="Діагностика ходової частини" ),
     *      @OA\Property(property="coefficient", title="coefficient", example=0.5 ),
     *      @OA\Property(property="priceWithVAT", title="priceWithVAT", example="823,00" ),
     *      @OA\Property(property="priceWithoutVAT", title="priceWithoutVAT", example="685,83" ),
     *      @OA\Property(property="amountWithoutVAT", title="amountWithoutVAT", example="257,18" ),
     *      @OA\Property(property="price", title="price", example="514,36" ),
     *      @OA\Property(property="amountIncludingVAT", title="amountIncludingVAT", example="308,62" ),
     *      @OA\Property(property="rate", title="rate", example=25.001215 ),
     *  ))
     * @OA\Property(property="AmountInWords", title="AmountInWords", example="Сім тисяч двісті шістдесят шість гривень нуль копійок")
     * @OA\Property(property="date", title="date", example="13 вересня 2021 р.")
     * @OA\Property(property="mileage", title="mileage", example=70557)
     * @OA\Property(property="currentAccount", title="currentAccount", example="Р/р 26009056232699 в ПАТ КБ ПриватБанк иной в м.г. Киев МФО 380269    код ЄДРПОУ 3352611854")
     * @OA\Property(property="owner", title="owner", type="object",
     *      @OA\Property(property="name", title="name", example="Рильська Тетяна Олександрівна" ),
     *      @OA\Property(property="phone", title="phone", example="+380939838323", ),
     *      @OA\Property(property="address", title="address", example="Світанкова, будинок №5" ),
     *      @OA\Property(property="email", title="email", example="example@gmail.com" ),
     *      @OA\Property(property="etc", title="etc", example="" ),
     *      @OA\Property(property="certificate", title="certificate", example="" ),
     *  )
     * @OA\Property(property="partsAmountIncludingVAT", title="partsAmountIncludingVAT", example="5414,29")
     * @OA\Property(property="customer", title="customer", type="object",
     *      @OA\Property(property="name", title="name", example="Рильська Тетяна Олександрівна" ),
     *      @OA\Property(property="FIO", title="FIO", example="Рильська Т.О." ),
     *      @OA\Property(property="phone", title="phone", example="+380939838323" ),
     *      @OA\Property(property="email", title="email", example="example@gmail.con" ),
     *      @OA\Property(property="date", title="date", example="" ),
     *      @OA\Property(property="number", title="number", example="" ),
     *  )
     * @OA\Property(property="model", title="model", example="OUTLANDER")
     * @OA\Property(property="bodyNumber", title="bodyNumber", example="JA4AD3A33HZ001924")
     * @OA\Property(property="dateOfSale", title="dateOfSale", example="03.06.2016")
     * @OA\Property(property="stateNumber", title="stateNumber", example="AI8688IA")
     * @OA\Property(property="producer", title="producer", example="QB")
     * @OA\Property(property="dispatcher", title="dispatcher", type="object",
     *      @OA\Property(property="position", title="position", example="Сервіс-консультант Митсубиси" ),
     *      @OA\Property(property="name", title="name", example="" ),
     *      @OA\Property(property="date", title="date", example="" ),
     *      @OA\Property(property="number", title="number", example="" ),
     *      @OA\Property(property="FIO", title="FIO", example="" ),
     *  )
     * @OA\Property(property="parts", title="parts", type="array",  @OA\Items(
     *      @OA\Property(property="unit", title="unit", example="шт" ),
     *      @OA\Property(property="producer", title="producer", example="MITSUBISHI" ),
     *      @OA\Property(property="ref", title="ref", example="MR968274" ),
     *      @OA\Property(property="name", title="name", example="ФІЛЬТР ПОВІТРЯНИЙ" ),
     *      @OA\Property(property="price", title="price", example="946,08" ),
     *      @OA\Property(property="quantity", title="quantity", example="1,00" ),
     *      @OA\Property(property="priceWithVAT", title="priceWithVAT", example="1261,44" ),
     *      @OA\Property(property="priceWithoutVAT", title="priceWithoutVAT", example="1051,20" ),
     *      @OA\Property(property="rate", title="rate", example=9.999683 ),
     *      @OA\Property(property="amountWithoutVAT", title="amountWithoutVAT", example="946,08" ),
     *      @OA\Property(property="amountIncludingVAT", title="amountIncludingVAT", example="1135,30" ),
     *  ))
     * @OA\Property(property="disassembledParts", title="disassembledParts", example="13.09.2021")
     * @OA\Property(property="AmountIncludingVAT", title="AmountIncludingVAT", example="7266,00")
     * @OA\Property(property="recommendations", title="recommendations", example="")
     * @OA\Property(property="AmountVAT", title="AmountVAT", example="1211,02")
     * @OA\Property(property="discountParts", title="discountParts", example="591,47")
     * @OA\Property(property="discountJobs", title="discountJobs", example="617,29")
     * @OA\Property(property="discount", title="discount", example="1208,76")
     * @OA\Property(property="jobsAmountWithoutVAT", title="jobsAmountWithoutVAT", example="1543,08")
     * @OA\Property(property="jobsAmountIncludingVAT", title="jobsAmountIncludingVAT", example="1851,71")
     * @OA\Property(property="partsAmountWithoutVAT", title="partsAmountWithoutVAT", example="4511,90")
     * @OA\Property(property="partsAmountVAT", title="partsAmountVAT", example="902,39")
     * @OA\Property(property="AmountWithoutVAT", title="AmountWithoutVAT", example="6054,98")
     */
}


