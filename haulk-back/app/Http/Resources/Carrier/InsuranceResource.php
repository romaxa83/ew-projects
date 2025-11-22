<?php


namespace App\Http\Resources\Carrier;

use App\Http\Resources\Files\ImageResource;
use App\Models\Saas\Company\CompanyInsuranceInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="CarrierInsuranceResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Carrier insurance data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"insurance_expiration_date", "insurance_cargo_limit", "insurance_deductible", "insurance_agent_name", "insurance_agent_phone"},
     *                        @OA\Property(property="insurance_expiration_date", type="string", description="Insurance expiration date"),
     *                        @OA\Property(property="insurance_cargo_limit", type="string", description="Insurance cargo limit"),
     *                        @OA\Property(property="insurance_deductible", type="string", description="Insurance deductible"),
     *                        @OA\Property(property="insurance_agent_name", type="string", description="Insurance agent name"),
     *                        @OA\Property(property="insurance_agent_phone", type="string", description="Insurance agent phone"),
     *                        @OA\Property(property="insurance_certificate_image", type="object", description="Carrier insurance link", allOf={
     *                              @OA\Schema(ref="#/components/schemas/Image")
     *                        }),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'insurance_expiration_date' => $this->insurance_expiration_date,
            'insurance_cargo_limit' => $this->insurance_cargo_limit,
            'insurance_deductible' => $this->insurance_deductible,
            'insurance_agent_name' => $this->insurance_agent_name,
            'insurance_agent_phone' => $this->insurance_agent_phone,
            CompanyInsuranceInfo::INSURANCE_FIELD_CARRIER => ImageResource::make(
                $this->getFirstMedia(CompanyInsuranceInfo::INSURANCE_FIELD_CARRIER)
            ),
        ];
    }

}
